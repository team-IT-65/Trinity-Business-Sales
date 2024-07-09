<?php

namespace GoDaddy\WordPress\MWC\Core\Auth\Providers\Platform\Cache\Types;

use GoDaddy\WordPress\MWC\Common\Cache\Cache;
use GoDaddy\WordPress\MWC\Common\Cache\Contracts\CacheableContract;

/**
 * Managed WooCommerce site access token cache handler class.
 */
class TokenCache extends Cache implements CacheableContract
{
    /** @var int how long in seconds should the cache be kept for */
    protected $expires = 3600;

    /**
     * Constructor.
     *
     * @param int $userId
     * @deprecated
     */
    final public function __construct(int $userId = null)
    {
        $this->type('platform_jwt');
        $this->key($userId ? "platform_jwt_{$userId}" : 'platform_jwt_site');
    }

    /**
     * Creates a new MWC token cache for a given user ID.
     *
     * @param int $userId
     * @return TokenCache
     */
    public static function for(int $userId = null)
    {
        return new static($userId);
    }
}
