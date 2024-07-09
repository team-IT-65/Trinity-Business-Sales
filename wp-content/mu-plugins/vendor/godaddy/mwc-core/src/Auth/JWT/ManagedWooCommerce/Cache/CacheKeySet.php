<?php

namespace GoDaddy\WordPress\MWC\Core\Auth\JWT\ManagedWooCommerce\Cache;

use GoDaddy\WordPress\MWC\Common\Cache\Cache;
use GoDaddy\WordPress\MWC\Common\Cache\Contracts\CacheableContract;
use GoDaddy\WordPress\MWC\Common\Traits\IsSingletonTrait;

/**
 * JWK cache handler class.
 */
class CacheKeySet extends Cache implements CacheableContract
{
    use IsSingletonTrait;

    /** @var int how long in seconds should the cache be kept for */
    protected $expires = 2 * HOUR_IN_SECONDS;

    /** @var string the cache key */
    protected $key = 'platform_jwks';

    /**
     * Constructor.
     */
    final public function __construct()
    {
        $this->type($this->key);
    }
}
