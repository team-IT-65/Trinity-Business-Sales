<?php

namespace GoDaddy\WordPress\MWC\Core\Auth\Sso\WordPress\Events;

use GoDaddy\WordPress\MWC\Common\Models\User;

/**
 * Event fired when a SSO user account is created.
 */
class SsoUserCreatedEvent extends AbstractSsoUserEvent
{
    /**
     * Event constructor.
     *
     * @param User $user
     */
    public function __construct(User $user)
    {
        parent::__construct($user);

        $this->action = 'create';
    }
}
