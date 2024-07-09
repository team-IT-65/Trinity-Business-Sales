<?php

namespace GoDaddy\WordPress\MWC\Core\Auth\Sso\WordPress\Traits;

use WP_User_Query;

/**
 * Trait for getting a WP_User_Query instance.
 */
trait CanGetWpUserQueryTrait
{
    /**
     * Gets a WordPress User Query instance for given arguments.
     *
     * @param array<string, mixed> $args
     * @return WP_User_Query
     */
    public function getWpUserQuery(array $args) : WP_User_Query
    {
        return new WP_User_Query($args);
    }
}
