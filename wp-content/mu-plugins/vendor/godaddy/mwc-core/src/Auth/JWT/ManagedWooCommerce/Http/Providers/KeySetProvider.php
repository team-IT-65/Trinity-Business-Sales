<?php

namespace GoDaddy\WordPress\MWC\Core\Auth\JWT\ManagedWooCommerce\Http\Providers;

use Exception;
use GoDaddy\WordPress\MWC\Common\Auth\JWT\Contracts\KeySetProviderContract;
use GoDaddy\WordPress\MWC\Common\Helpers\ArrayHelper;
use GoDaddy\WordPress\MWC\Common\Helpers\TypeHelper;
use GoDaddy\WordPress\MWC\Common\Traits\CanGetNewInstanceTrait;
use GoDaddy\WordPress\MWC\Core\Auth\JWT\ManagedWooCommerce\Cache\CacheKeySet;
use GoDaddy\WordPress\MWC\Core\Auth\JWT\ManagedWooCommerce\Http\Exceptions\KeySetRequestException;
use GoDaddy\WordPress\MWC\Core\Auth\JWT\ManagedWooCommerce\Http\Requests\KeySetRequest;

class KeySetProvider implements KeySetProviderContract
{
    use CanGetNewInstanceTrait;

    /**
     * {@inheritDoc}
     * @throws KeySetRequestException
     */
    public function getKeySet() : array
    {
        $cache = CacheKeySet::getInstance();

        if ($cachedBody = TypeHelper::array($cache->get(), [])) {
            return $cachedBody;
        }

        try {
            $response = KeySetRequest::getNewInstance()->send();
        } catch (Exception $e) {
            throw new KeySetRequestException('Failed to get keyset: '.$e->getMessage(), $e);
        }

        $body = TypeHelper::array($response->getBody(), []);

        if (! empty(ArrayHelper::get($body, 'keys'))) {
            $cache->set($body);
        }

        return $body;
    }
}
