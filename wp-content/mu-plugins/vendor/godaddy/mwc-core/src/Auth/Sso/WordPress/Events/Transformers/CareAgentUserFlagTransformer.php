<?php

namespace GoDaddy\WordPress\MWC\Core\Auth\Sso\WordPress\Events\Transformers;

use GoDaddy\WordPress\MWC\Common\Events\AbstractEventTransformer;
use GoDaddy\WordPress\MWC\Common\Events\Contracts\EventBridgeEventContract;
use GoDaddy\WordPress\MWC\Common\Events\Contracts\EventContract;
use GoDaddy\WordPress\MWC\Common\Helpers\ArrayHelper;
use GoDaddy\WordPress\MWC\Common\Models\User;
use GoDaddy\WordPress\MWC\Core\Auth\Sso\WordPress\Services\Contracts\CareUserServiceContract;
use GoDaddy\WordPress\MWC\Core\Auth\Sso\WordPress\WordPressSso;

/**
 * Adds an `isCareAgent` flag to the `user` property on all events, if the current user is a care agent.
 * This allows us to identify which actions were performed by a care agent versus a regular merchant.
 */
class CareAgentUserFlagTransformer extends AbstractEventTransformer
{
    protected CareUserServiceContract $careUserService;

    public function __construct(CareUserServiceContract $careUserService)
    {
        $this->careUserService = $careUserService;
    }

    /**
     * Determines whether we should transform the provided event.
     *
     * @param EventContract $event
     * @return bool
     */
    public function shouldHandle(EventContract $event) : bool
    {
        return $event instanceof EventBridgeEventContract && WordPressSso::shouldLoad() && $this->isCareUserAccount();
    }

    /**
     * Adds the `isCareAgent` flag to the event data, if applicable.
     *
     * @param EventBridgeEventContract $event
     * @return void
     */
    public function handle(EventContract $event)
    {
        $data = $event->getData();
        ArrayHelper::set($data, 'user.isCareAgent', true);
        $event->setData($data);
    }

    /**
     * Determines whether the current user is a care agent.
     *
     * @return bool
     */
    protected function isCareUserAccount() : bool
    {
        $currentUser = User::getCurrent();

        return $currentUser && $this->careUserService->isCareUserAccount((int) $currentUser->getId());
    }
}
