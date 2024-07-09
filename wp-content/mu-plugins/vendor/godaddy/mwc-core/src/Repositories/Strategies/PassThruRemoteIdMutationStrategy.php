<?php

namespace GoDaddy\WordPress\MWC\Core\Repositories\Strategies;

use GoDaddy\WordPress\MWC\Common\Traits\CanGetNewInstanceTrait;
use GoDaddy\WordPress\MWC\Core\Repositories\Strategies\Contracts\RemoteIdStrategyContract;

class PassThruRemoteIdMutationStrategy implements RemoteIdStrategyContract
{
    use CanGetNewInstanceTrait;

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
        return $value;
    }
}
