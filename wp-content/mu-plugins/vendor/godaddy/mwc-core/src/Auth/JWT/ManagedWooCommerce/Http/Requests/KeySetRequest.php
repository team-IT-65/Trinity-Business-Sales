<?php

namespace GoDaddy\WordPress\MWC\Core\Auth\JWT\ManagedWooCommerce\Http\Requests;

use Exception;
use GoDaddy\WordPress\MWC\Common\Helpers\StringHelper;
use GoDaddy\WordPress\MWC\Common\Http\Request;
use GoDaddy\WordPress\MWC\Common\Repositories\ManagedWooCommerceRepository;
use GoDaddy\WordPress\MWC\Common\Traits\CanGetNewInstanceTrait;

/**
 * The request to get JWK from the MWC API.
 */
class KeySetRequest extends Request
{
    use CanGetNewInstanceTrait;

    /**
     * Request constructor.
     *
     * @throws Exception
     */
    public function __construct()
    {
        parent::__construct($this->getUrlOfMwcApiJWKS());
    }

    /**
     * Get MWC API's KeySet URL.
     * @note uses MWC API base URL with version removed (jwks endpoint is not versioned)
     *
     * @return string
     */
    protected function getUrlOfMwcApiJWKS() : string
    {
        return StringHelper::trailingSlash(
            StringHelper::beforeLast(ManagedWooCommerceRepository::getApiUrl(), '/v')
        ).'.well-known/jwks.json';
    }
}
