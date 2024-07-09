<?php

namespace GoDaddy\WordPress\MWC\Core\Auth\Sso\WordPress\Events;

use GoDaddy\WordPress\MWC\Common\Events\Contracts\EventBridgeEventContract;
use GoDaddy\WordPress\MWC\Common\Models\User;
use GoDaddy\WordPress\MWC\Common\Traits\CanGetNewInstanceTrait;
use GoDaddy\WordPress\MWC\Common\Traits\IsEventBridgeEventTrait;

/**
 * Abstract class for handling Care Agent user events.
 *
 * @method static static getNewInstance(User $user)
 */
abstract class AbstractSsoUserEvent implements EventBridgeEventContract
{
    use IsEventBridgeEventTrait;
    use CanGetNewInstanceTrait;

    /** @var User Care Agent user */
    protected User $user;

    /**
     * Event constructor.
     *
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
        $this->resource = 'sso_user';
    }

    /**
     * Gets the user object associated with the event.
     *
     * @return User
     */
    public function getUser() : User
    {
        return $this->user;
    }
}
