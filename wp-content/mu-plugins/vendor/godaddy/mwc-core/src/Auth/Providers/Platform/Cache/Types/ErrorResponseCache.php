<?php

namespace GoDaddy\WordPress\MWC\Core\Auth\Providers\Platform\Cache\Types;

use GoDaddy\WordPress\MWC\Common\Cache\Cache;
use GoDaddy\WordPress\MWC\Common\Cache\Contracts\CacheableContract;
use GoDaddy\WordPress\MWC\Common\Traits\IsSingletonTrait;

/**
 * Cache for errors received trying to authenticate against the MWC API.
 */
class ErrorResponseCache extends Cache implements CacheableContract
{
    use IsSingletonTrait;

    /** @var string the type of object we are caching */
    protected $type = 'platform_jwt_error';

    /** @var int how long in seconds should the cache be kept for */
    protected $expires = 900;

    /**
     * Constructor.
     *
     * @deprecated
     */
    public function __construct()
    {
        $this->key($this->type);
    }
}
