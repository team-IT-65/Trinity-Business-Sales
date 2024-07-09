<?php

namespace GoDaddy\WordPress\MWC\Core\Auth\Sso\WordPress\Interceptors\Handlers;

use GoDaddy\WordPress\MWC\Common\DataSources\WordPress\Adapters\UserAdapter;
use GoDaddy\WordPress\MWC\Common\Events\Events;
use GoDaddy\WordPress\MWC\Common\Helpers\ArrayHelper;
use GoDaddy\WordPress\MWC\Common\Models\User;
use GoDaddy\WordPress\MWC\Core\Auth\Sso\WordPress\Events\CareUserLogInEvent;
use GoDaddy\WordPress\MWC\Core\Auth\Sso\WordPress\Interceptors\CareAgentLoginInterceptor;
use GoDaddy\WordPress\MWC\Core\Auth\Sso\WordPress\Services\Contracts\CareUserServiceContract;
use GoDaddy\WordPress\MWC\Core\Interceptors\Handlers\AbstractInterceptorHandler;
use WP_User;

/**
 * Handler for the {@see CareAgentLoginInterceptor}.
 */
class CareAgentLoginInterceptorHandler extends AbstractInterceptorHandler
{
    protected CareUserServiceContract $careUserService;

    /**
     * Constructor.
     *
     * @param CareUserServiceContract $careUserService
     */
    public function __construct(CareUserServiceContract $careUserService)
    {
        $this->careUserService = $careUserService;
    }

    /**
     * Broadcasts an event if the account that just logged in is for a care agent.
     *
     * @param ...$args
     * @return void
     */
    public function run(...$args)
    {
        $user = ArrayHelper::get($args, 1);

        if (! $user instanceof WP_User) {
            return;
        }

        if ($this->careUserService->isCareUserAccount($user->ID)) {
            $this->broadcastEvent($user);
        }
    }

    /**
     * Broadcasts a {@see CareUserLogInEvent} for the provided user.
     *
     * @param WP_User $user
     * @return void
     */
    protected function broadcastEvent(WP_User $user) : void
    {
        Events::broadcast(
            CareUserLogInEvent::getNewInstance(User::seed(UserAdapter::getNewInstance($user)->convertFromSource()))
        );
    }
}
