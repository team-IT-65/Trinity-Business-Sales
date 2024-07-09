<?php

namespace GoDaddy\WordPress\MWC\Core\Auth\Sso\WordPress\Interceptors;

use Exception;
use GoDaddy\WordPress\MWC\Common\Interceptors\AbstractInterceptor;
use GoDaddy\WordPress\MWC\Common\Register\Register;
use GoDaddy\WordPress\MWC\Core\Auth\Sso\WordPress\Interceptors\Handlers\CareAgentLoginInterceptorHandler;

/**
 * Intercepts a login event by the Care Agent user.
 */
class CareAgentLoginInterceptor extends AbstractInterceptor
{
    /**
     * Registers the hook to log in the care agent user.
     *
     * @return void
     * @throws Exception
     */
    public function addHooks() : void
    {
        Register::action()
            ->setGroup('wp_login')
            ->setPriority(PHP_INT_MAX)
            ->setHandler([CareAgentLoginInterceptorHandler::class, 'handle'])
            ->setArgumentsCount(2)
            ->execute();
    }
}
