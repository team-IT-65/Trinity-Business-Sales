<?php

namespace GoDaddy\WordPress\MWC\Core\Auth\Providers\Platform;

use Exception;
use GoDaddy\WordPress\MWC\Common\Auth\Providers\AbstractTokenAuthProvider;
use GoDaddy\WordPress\MWC\Common\Cache\Contracts\CacheableContract;
use GoDaddy\WordPress\MWC\Common\Configuration\Configuration;
use GoDaddy\WordPress\MWC\Common\Http\Request;
use GoDaddy\WordPress\MWC\Common\Platforms\PlatformRepositoryFactory;
use GoDaddy\WordPress\MWC\Core\Auth\Providers\Platform\Cache\Types\ErrorResponseCache;
use GoDaddy\WordPress\MWC\Core\Auth\Providers\Platform\Cache\Types\TokenCache;

class AuthProvider extends AbstractTokenAuthProvider
{
    /**
     * {@inheritDoc}
     */
    protected function getCredentialsCache() : CacheableContract
    {
        return TokenCache::for($this->getCurrentUserId());
    }

    /**
     * {@inheritDoc}
     */
    protected function getCredentialsErrorCache() : CacheableContract
    {
        return ErrorResponseCache::getInstance();
    }

    /**
     * {@inheritDoc}
     * @throws Exception
     */
    protected function getCredentialsRequestInstance() : Request
    {
        $request = new Request();

        try {
            $request->addHeaders([
                'X-Account-UID' => Configuration::get('godaddy.account.uid', ''),
                'X-Site-Token'  => Configuration::get('godaddy.site.token', ''),
                'X-Source'      => PlatformRepositoryFactory::getNewInstance()->getPlatformRepository()->getPlatformName(),
            ]);
        } catch (Exception $exception) {
            // ignore exception from ArrayHelper::combine() that is not possible when both parameters are arrays.
            // The two parameters in this case are the headers property of the request and the array of new headers.
        }

        return $request;
    }
}
