<?php

namespace GoDaddy\WordPress\MWC\Core\Auth\Sso\WordPress\Interceptors;

use Exception;
use GoDaddy\WordPress\MWC\Common\Interceptors\AbstractInterceptor;
use GoDaddy\WordPress\MWC\Common\Register\Register;
use GoDaddy\WordPress\MWC\Core\Auth\Sso\WordPress\Interceptors\Handlers\SsoInterceptorHandler;

/**
 * Intercepts a request with the SSO query args set, and attempts to sign the user on.
 */
class SsoInterceptor extends AbstractInterceptor
{
    /**
     * Registers the hook to handle SSO.
     *
     * @return void
     * @throws Exception
     */
    public function addHooks() : void
    {
        Register::action()
            ->setGroup('init')
            ->setHandler([SsoInterceptorHandler::class, 'handle'])
            ->setPriority(10)
            ->execute();
    }
}
