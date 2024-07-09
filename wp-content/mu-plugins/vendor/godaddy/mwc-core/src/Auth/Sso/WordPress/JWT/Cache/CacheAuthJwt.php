<?php

namespace GoDaddy\WordPress\MWC\Core\Auth\Sso\WordPress\JWT\Cache;

use GoDaddy\WordPress\MWC\Common\Cache\Cache;
use GoDaddy\WordPress\MWC\Common\Cache\Contracts\CacheableContract;
use GoDaddy\WordPress\MWC\Common\Traits\CanGetNewInstanceTrait;

/**
 * Cache for the SSO JWT.
 */
class CacheAuthJwt extends Cache implements CacheableContract
{
    use CanGetNewInstanceTrait;

    /** @var int how long in seconds should the cache be kept for. Should be >= TTL of an SSO token. {@see TokenNotExpiredRule} */
    protected $expires = 2 * MINUTE_IN_SECONDS;

    /** @var string */
    protected $key = 'sso_jwt_';

    /**
     * Constructor.
     *
     * @param string $jti the JWT ID
     */
    final public function __construct(string $jti)
    {
        $this->key($this->key.$jti);
    }
}
