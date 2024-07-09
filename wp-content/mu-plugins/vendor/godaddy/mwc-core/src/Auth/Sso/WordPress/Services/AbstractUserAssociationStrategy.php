<?php

namespace GoDaddy\WordPress\MWC\Core\Auth\Sso\WordPress\Services;

use GoDaddy\WordPress\MWC\Core\Auth\Sso\WordPress\JWT\Contracts\SsoTokenContract;
use GoDaddy\WordPress\MWC\Core\Auth\Sso\WordPress\Services\Contracts\UserAssociationStrategyContract;

/**
 * Abstract for user association strategies that accept an {@see SsoTokenContract} to make associations.
 */
abstract class AbstractUserAssociationStrategy implements UserAssociationStrategyContract
{
    protected SsoTokenContract $token;

    public function __construct(SsoTokenContract $token)
    {
        $this->token = $token;
    }
}
