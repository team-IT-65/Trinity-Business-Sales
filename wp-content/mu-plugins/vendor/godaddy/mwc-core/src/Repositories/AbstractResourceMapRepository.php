<?php

namespace GoDaddy\WordPress\MWC\Core\Repositories;

use Exception;
use GoDaddy\WordPress\MWC\Common\Exceptions\SentryException;
use GoDaddy\WordPress\MWC\Common\Exceptions\WordPressDatabaseException;
use GoDaddy\WordPress\MWC\Common\Helpers\TypeHelper;
use GoDaddy\WordPress\MWC\Common\Repositories\WordPress\DatabaseRepository;
use GoDaddy\WordPress\MWC\Core\Features\Commerce\Enums\CommerceTableColumns;
use GoDaddy\WordPress\MWC\Core\Features\Commerce\Enums\CommerceTables;
use GoDaddy\WordPress\MWC\Core\Features\Commerce\Exceptions\Contracts\CommerceExceptionContract;
use GoDaddy\WordPress\MWC\Core\Features\Commerce\Models\Contracts\CommerceContextContract;
use GoDaddy\WordPress\MWC\Core\Features\Commerce\Models\ResourceMap;
use GoDaddy\WordPress\MWC\Core\Features\Commerce\Models\ResourceMapCollection;
use GoDaddy\WordPress\MWC\Core\Features\Commerce\Repositories\AbstractResourceRepository;
use GoDaddy\WordPress\MWC\Core\Features\Commerce\Repositories\CommerceContextRepository;
use GoDaddy\WordPress\MWC\Core\Features\Commerce\Services\Exceptions\CachingStrategyException;
use GoDaddy\WordPress\MWC\Core\Features\Commerce\Services\Exceptions\ResourceMapNotFoundException;
use GoDaddy\WordPress\MWC\Core\Features\Commerce\Services\ResourceMaps\ResourceMapCachingServiceRouter;
use GoDaddy\WordPress\MWC\Core\Repositories\Strategies\Contracts\RemoteIdStrategyContract;

/**
 * Abstract resource map repository.
 *
 * @phpstan-type TResourceMapRow array{id: numeric-string, commerce_id: string, local_id: numeric-string}
 */
abstract class AbstractResourceMapRepository extends AbstractResourceRepository
{
    /** @var string commerce map IDs (uuids, ksuids) table name */
    public const MAP_IDS_TABLE = 'godaddy_mwc_commerce_map_ids';

    /** @var string commerce resource type table name */
    public const RESOURCE_TYPES_TABLE = 'godaddy_mwc_commerce_map_resource_types';

    /** @const string COLUMN_COMMERCE_ID = 'commerce_id' column storing the remote commerce IDs */
    public const COLUMN_COMMERCE_ID = 'commerce_id';

    /** @var string column storing the primary ID of the map's row */
    public const COLUMN_ID = 'id';

    /** @const string COLUMN_LOCAL_ID = 'local_id' column storing the local IDs */
    public const COLUMN_LOCAL_ID = 'local_id';

    /** @var string column storing the resource type IDs */
    public const COLUMN_RESOURCE_TYPE_ID = 'resource_type_id';

    /** @var string column storing the commerce context IDs */
    public const COLUMN_COMMERCE_CONTEXT_ID = 'commerce_context_id';

    /** @var ResourceMapCachingServiceRouter caching service */
    protected ResourceMapCachingServiceRouter $resourceMapCachingServiceRouter;

    public function __construct(
        CommerceContextContract $commerceContext,
        ResourceMapCachingServiceRouter $resourceMapCachingServiceRouter,
        ?RemoteIdStrategyContract $remoteIdMutationStrategy = null
    ) {
        parent::__construct($commerceContext, $remoteIdMutationStrategy);

        $this->resourceMapCachingServiceRouter = $resourceMapCachingServiceRouter->setResourceType($this->resourceType);
    }

    /**
     * Adds a new map to associate the local ID with the given remote UUID.
     *
     * @param int $localId
     * @param string $remoteId
     * @return void
     * @throws WordPressDatabaseException
     */
    public function add(int $localId, string $remoteId) : void
    {
        $args = [
            static::COLUMN_LOCAL_ID            => $localId,
            static::COLUMN_COMMERCE_ID         => $this->remoteIdMutationStrategy->getRemoteIdForDatabase($remoteId),
            static::COLUMN_RESOURCE_TYPE_ID    => $this->getResourceTypeId(),
            static::COLUMN_COMMERCE_CONTEXT_ID => $this->getContextId(),
        ];

        $rowId = DatabaseRepository::insert(static::MAP_IDS_TABLE, $args);

        try {
            $merged = array_merge($args, [static::COLUMN_ID => $rowId]);
            /** @var array{id: numeric-string, commerce_id: string, local_id: numeric-string} $row */
            $row = [
                'id'          => (string) $merged[static::COLUMN_ID],
                'commerce_id' => (string) $merged[static::COLUMN_COMMERCE_ID],
                'local_id'    => (string) $merged[static::COLUMN_LOCAL_ID],
            ];

            $this->resourceMapCachingServiceRouter->set(ResourceMap::fromRow($row));
        } catch(Exception|CommerceExceptionContract $e) {
            SentryException::getNewInstance($e->getMessage(), $e);
        }
    }

    /**
     * Updates the remote ID of a row, if found by local ID, otherwise adds the map.
     *
     * Unlike {@see AbstractResourceMapRepository::add()}, this method does not attempt
     * to write to the database if an identical map already exists.
     *
     * @throws WordPressDatabaseException
     */
    public function addOrUpdateRemoteId(int $localId, string $remoteId) : void
    {
        $existingResourceMap = $this->getMappingByLocalId($localId);

        if (! $existingResourceMap) {
            $this->add($localId, $remoteId);
        } elseif ($remoteId !== $existingResourceMap->commerceId) {
            $formattedRemoteId = $this->remoteIdMutationStrategy->getRemoteIdForDatabase($remoteId);

            DatabaseRepository::update(
                AbstractResourceMapRepository::MAP_IDS_TABLE,
                [AbstractResourceMapRepository::COLUMN_COMMERCE_ID => $formattedRemoteId],
                [AbstractResourceMapRepository::COLUMN_ID          => $existingResourceMap->id],
                ['%s'],
                ['%d'],
            );

            try {
                $this->resourceMapCachingServiceRouter->set(
                    new ResourceMap($existingResourceMap->id, $formattedRemoteId, $localId)
                );
            } catch(Exception|CommerceExceptionContract $e) {
                SentryException::getNewInstance($e->getMessage(), $e);
            }
        }
    }

    /**
     * Finds the remote ID of a resource by its local ID.
     *
     * @param int $localId
     * @return string|null
     */
    public function getRemoteId(int $localId) : ?string
    {
        try {
            $resourceMap = $this->getMappingByLocalIdWithCache($localId);

            return $resourceMap ? $resourceMap->commerceId : null;
        } catch(Exception|CommerceExceptionContract $e) {
            return null;
        }
    }

    /**
     * Get a collection of resource maps by the given local IDs.
     *
     * @param int[] $localIds
     *
     * @return ResourceMapCollection
     */
    public function getMappingsByLocalIds(array $localIds) : ResourceMapCollection
    {
        $cachedResults = $this->getCachedResourceMapsByIds(static::COLUMN_LOCAL_ID, $localIds);

        if (count($cachedResults) === count($localIds)) {
            return new ResourceMapCollection($cachedResults);
        }

        $collection = ResourceMapCollection::fromRows($this->queryRowsByIds(self::COLUMN_LOCAL_ID, $localIds));

        try {
            $this->resourceMapCachingServiceRouter->setMany($collection->getResourceMaps());
        } catch(Exception|CommerceExceptionContract $e) {
            SentryException::getNewInstance($e->getMessage(), $e);
        }

        return $collection;
    }

    /**
     * Get a collection of resource maps by the given remote IDs.
     *
     * @param string[] $remoteIds
     *
     * @return ResourceMapCollection
     */
    public function getMappingsByRemoteIds(array $remoteIds) : ResourceMapCollection
    {
        $cachedResults = $this->getCachedResourceMapsByIds(static::COLUMN_COMMERCE_ID, $remoteIds);

        if (count($cachedResults) === count($remoteIds)) {
            return new ResourceMapCollection($cachedResults);
        }

        $collection = ResourceMapCollection::fromRows(
            $this->queryRowsByIds(
                self::COLUMN_COMMERCE_ID,
                array_map([$this->remoteIdMutationStrategy, 'getRemoteIdForDatabase'], $remoteIds)
            )
        );

        try {
            $this->resourceMapCachingServiceRouter->setMany($collection->getResourceMaps());
        } catch(Exception|CommerceExceptionContract $e) {
            SentryException::getNewInstance($e->getMessage(), $e);
        }

        return $collection;
    }

    /**
     * Finds the local ID of a resource by its remote UUID.
     *
     * @param string $remoteId
     *
     * @return int|null
     */
    public function getLocalId(string $remoteId) : ?int
    {
        try {
            $resourceMap = $this->getMappingByRemoteIdWithCache($remoteId);

            return $resourceMap->localId ?? null;
        } catch(Exception|CommerceExceptionContract $e) {
            return null;
        }
    }

    /**
     * Gets a SQL clause that can be used to perform an inner join on the contexts table.
     *
     * @param string $idMapTableNameAlias
     * @param string $contextsTableNameAlias
     * @return string
     */
    protected function getContextJoinClause(
        string $idMapTableNameAlias = 'map_ids',
        string $contextsTableNameAlias = 'contexts'
    ) : string {
        $contextsTableName = CommerceContextRepository::CONTEXT_TABLE;
        $storeId = TypeHelper::string(esc_sql($this->commerceContext->getStoreId()), '');

        return "INNER JOIN {$contextsTableName} AS {$contextsTableNameAlias}
        ON {$contextsTableNameAlias}.id = {$idMapTableNameAlias}.".static::COLUMN_COMMERCE_CONTEXT_ID."
        AND {$contextsTableNameAlias}.gd_store_id = '{$storeId}'";
    }

    /**
     * Gets the context ID.
     *
     * @return int|null
     */
    protected function getContextId() : ?int
    {
        return $this->commerceContext->getId();
    }

    /**
     * Gets the map of the given resource local ID to a remote ID.
     */
    protected function getMappingByLocalId(int $localId) : ?ResourceMap
    {
        if (! $row = $this->getMappingRowByLocalId($localId)) {
            return null;
        }

        $row['commerce_id'] = TypeHelper::string($this->remoteIdMutationStrategy->formatRemoteIdFromDatabase($row['commerce_id']), '');

        return ResourceMap::fromRow($row);
    }

    /**
     * Gets the map of the given resource by local ID.
     *
     * This is the same as {@see static::getMappingByLocalId()}, but with a caching layer in front.
     *
     * @param int $localId
     * @return ResourceMap|null
     */
    protected function getMappingByLocalIdWithCache(int $localId) : ?ResourceMap
    {
        try {
            return $this->resourceMapCachingServiceRouter->rememberByLocalId(
                $localId,
                function () use ($localId) {
                    if (! $resourceMap = $this->getMappingByLocalId($localId)) {
                        throw new ResourceMapNotFoundException();
                    }

                    return $resourceMap;
                }
            );
        } catch(CachingStrategyException|CommerceExceptionContract $e) {
            SentryException::getNewInstance($e->getMessage(), $e);

            return null;
            /* @phpstan-ignore-next-line phpstan is unaware of the exception bubbling up from the closure */
        } catch(ResourceMapNotFoundException $e) {
            return null;
        }
    }

    /**
     * Gets the row representing a map of the given resource local ID to a remote ID.
     *
     * @return TResourceMapRow|array{}
     */
    protected function getMappingRowByLocalId(int $localId) : array
    {
        /** @var TResourceMapRow|array{} $row */
        $row = DatabaseRepository::getRow(
            implode(' ', [
                'SELECT map_ids.'.static::COLUMN_ID.', map_ids.'.static::COLUMN_COMMERCE_ID.', map_ids.'.static::COLUMN_LOCAL_ID.' FROM '.static::MAP_IDS_TABLE.' AS map_ids',
                $this->getResourceTypeJoinClause(),
                $this->getContextJoinClause(),
                'WHERE map_ids.'.static::COLUMN_LOCAL_ID.' = %d',
            ]),
            [$localId]
        );

        return $row;
    }

    /**
     * Gets the map of the given resource remote ID to a local ID.
     */
    protected function getMappingByRemoteId(string $remoteId) : ?ResourceMap
    {
        if (! $row = $this->getMappingRowByRemoteId($remoteId)) {
            return null;
        }

        $row['commerce_id'] = TypeHelper::string($this->remoteIdMutationStrategy->formatRemoteIdFromDatabase($row['commerce_id']), '');

        return ResourceMap::fromRow($row);
    }

    /**
     * Gets the map of the given resource by remote ID.
     *
     * This is the same as {@see static::getMappingByLocalId()}, but with a caching layer in front.
     *
     * @param string $remoteId
     * @return ResourceMap|null
     */
    protected function getMappingByRemoteIdWithCache(string $remoteId) : ?ResourceMap
    {
        try {
            return $this->resourceMapCachingServiceRouter->rememberByCommerceId(
                $remoteId,
                function () use ($remoteId) {
                    if (! $resourceMap = $this->getMappingByRemoteId($remoteId)) {
                        throw new ResourceMapNotFoundException();
                    }

                    return $resourceMap;
                }
            );
        } catch(CachingStrategyException|CommerceExceptionContract $e) {
            SentryException::getNewInstance($e->getMessage(), $e);

            return null;
            /* @phpstan-ignore-next-line phpstan is unaware of the exception bubbling up from the closure */
        } catch(ResourceMapNotFoundException $e) {
            return null;
        }
    }

    /**
     * Gets the row representing a map of the given resource remote ID to a local ID.
     *
     * @return TResourceMapRow|array{}
     */
    protected function getMappingRowByRemoteId(string $remoteId) : array
    {
        /** @var TResourceMapRow|array{} $row */
        $row = DatabaseRepository::getRow(
            implode(' ', [
                'SELECT map_ids.'.static::COLUMN_ID.', map_ids.'.static::COLUMN_COMMERCE_ID.', map_ids.'.static::COLUMN_LOCAL_ID.' FROM '.static::MAP_IDS_TABLE.' AS map_ids',
                $this->getResourceTypeJoinClause(),
                $this->getContextJoinClause(),
                'WHERE map_ids.'.static::COLUMN_COMMERCE_ID.' = %s',
            ]),
            [$this->remoteIdMutationStrategy->getRemoteIdForDatabase($remoteId)]
        );

        return $row;
    }

    /**
     * Get a printf-compatible placeholder for the given column name.
     *
     * @param static::COLUMN_* $columnName
     *
     * @return ($columnName is static::COLUMN_COMMERCE_ID ? '%s' : '%d')
     */
    protected function getPlaceholderForColumn(string $columnName) : string
    {
        if (static::COLUMN_COMMERCE_ID === $columnName) {
            return '%s';
        }

        return '%d';
    }

    /**
     * Query the database to select rows where the given column matches any of the values.
     *
     * @param static::COLUMN_COMMERCE_ID|static::COLUMN_LOCAL_ID $columnName
     * @param array<int|string> $values
     * @return TResourceMapRow[]
     */
    protected function queryRowsByIds(string $columnName, array $values) : array
    {
        if (! $values) {
            return [];
        }

        /** @var TResourceMapRow[] $results */
        $results = DatabaseRepository::getResults($this->getQueryRowsByIdsSql($columnName, $values), $values);

        return array_map([$this, 'formatRemoteIdFromDatabase'], $results);
    }

    /**
     * Gets the cached resource map objects by their IDs.
     *
     * @param static::COLUMN_COMMERCE_ID|static::COLUMN_LOCAL_ID $columnName
     * @param array<int|string> $values array of local IDs or commerce IDs
     * @return ResourceMap[]
     */
    protected function getCachedResourceMapsByIds(string $columnName, array $values) : array
    {
        $cachedResults = [];
        if ($columnName === static::COLUMN_LOCAL_ID) {
            $cachedResults = $this->resourceMapCachingServiceRouter->getManyByLocalIds(TypeHelper::arrayOfIntegers($values, false));
        } elseif ($columnName === static::COLUMN_COMMERCE_ID) {
            $cachedResults = $this->resourceMapCachingServiceRouter->getManyByCommerceIds(TypeHelper::arrayOfStrings($values, false));
        }

        return $cachedResults;
    }

    /**
     * Gets the SQL necessary to query the database to select rows where the given column matches any of the values.
     *
     * @param static::COLUMN_COMMERCE_ID|static::COLUMN_LOCAL_ID $columnName
     * @param non-empty-array<int|string> $values
     * @return non-empty-string
     */
    protected function getQueryRowsByIdsSql(string $columnName, array $values) : string
    {
        $idPlaceholders = implode(',', array_fill(0, count($values), $this->getPlaceholderForColumn($columnName)));

        return implode(' ', [
            'SELECT map_ids.'.static::COLUMN_ID.', map_ids.'.static::COLUMN_COMMERCE_ID.', map_ids.'.static::COLUMN_LOCAL_ID.' FROM '.static::MAP_IDS_TABLE.' AS map_ids',
            $this->getResourceTypeJoinClause(),
            $this->getContextJoinClause(),
            "WHERE map_ids.{$columnName} IN ({$idPlaceholders})",
        ]);
    }

    /**
     * Formats the remote ID of the given row.
     *
     * @param TResourceMapRow $row
     * @return TResourceMapRow
     */
    protected function formatRemoteIdFromDatabase(array $row) : array
    {
        $row['commerce_id'] = (string) $this->remoteIdMutationStrategy->formatRemoteIdFromDatabase($row['commerce_id']);

        return $row;
    }

    /**
     * Deletes a mapping row by the provided local ID.
     *
     * @param int $localId
     * @return int number of records that were deleted
     * @throws WordPressDatabaseException
     */
    public function deleteByLocalId(int $localId) : int
    {
        $mapping = $this->getMappingByLocalId($localId);

        if ($mapping) {
            $numberRowsDeleted = DatabaseRepository::delete(
                static::MAP_IDS_TABLE,
                ['id' => $mapping->id],
                ['%d']
            );

            try {
                $this->resourceMapCachingServiceRouter->remove($mapping);
            } catch(Exception $e) {
                // an exception here does not need to halt the whole process
            }
        } else {
            $numberRowsDeleted = 0;
        }

        return TypeHelper::int($numberRowsDeleted, 0);
    }

    /**
     * Gets a SQL query that can be used to select all `local_id` values from the table for a specific resource type ID.
     * e.g. `SELECT local_id FROM godaddy_mwc_commerce_map_ids WHERE resource_type_id = 11`.
     *
     * @return string
     */
    protected function getMappedLocalIdsForResourceTypeQuery() : string
    {
        return '
            SELECT '.CommerceTableColumns::LocalId.'
            FROM '.CommerceTables::ResourceMap.'
            WHERE '.CommerceTableColumns::ResourceTypeId.' = %d
        ';
    }
}
