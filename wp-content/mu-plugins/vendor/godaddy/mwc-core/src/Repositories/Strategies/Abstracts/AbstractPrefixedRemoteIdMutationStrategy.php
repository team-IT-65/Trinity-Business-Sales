<?php

namespace GoDaddy\WordPress\MWC\Core\Repositories\Strategies\Abstracts;

use GoDaddy\WordPress\MWC\Common\Helpers\StringHelper;
use GoDaddy\WordPress\MWC\Core\Repositories\Strategies\Contracts\RemoteIdStrategyContract;

class AbstractPrefixedRemoteIdMutationStrategy implements RemoteIdStrategyContract
{
    /** @var non-empty-string The prefix used by the input strings */
    protected string $prefix;

    /** @var non-empty-string The delimiter used to separate the prefix from the given ID */
    protected string $delimiter = '_';

    /**
     * {@inheritDoc}
     */
    public function formatRemoteIdFromDatabase(?string $value) : ?string
    {
        return $value ? $this->prefix.$this->delimiter.$value : $value;
    }

    /**
     * {@inheritDoc}
     */
    public function getRemoteIdForDatabase(string $value) : string
    {
        return StringHelper::after($value, $this->prefix.$this->delimiter);
    }
}
