<?php

namespace GoDaddy\WordPress\MWC\Core\Repositories\Strategies;

use GoDaddy\WordPress\MWC\Core\Features\Commerce\CreateCommerceMapIdsTableAction;
use GoDaddy\WordPress\MWC\Core\Repositories\Strategies\Contracts\RemoteIdStrategyContract;

/**
 * Mutation strategy that hashes the value stored in the database using SHA224.
 */
class HashedRemoteIdMutationStrategy implements RemoteIdStrategyContract
{
    /**
     * {@inheritDoc}
     */
    public function formatRemoteIdFromDatabase(?string $value) : ?string
    {
        return $value;
    }

    /**
     * {@inheritDoc}
     */
    public function getRemoteIdForDatabase(string $value) : string
    {
        /*
         * This algorithm is chosen so the value fits within our column size.
         * @see CreateCommerceMapIdsTableAction::createTable()
         */
        return hash('sha224', $value);
    }
}
