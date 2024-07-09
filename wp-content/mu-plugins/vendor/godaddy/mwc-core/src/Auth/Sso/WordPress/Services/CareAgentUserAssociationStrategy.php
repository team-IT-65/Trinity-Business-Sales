<?php

namespace GoDaddy\WordPress\MWC\Core\Auth\Sso\WordPress\Services;

use GoDaddy\WordPress\MWC\Common\Models\User;
use GoDaddy\WordPress\MWC\Core\Auth\Sso\WordPress\Contracts\CanCreateUserContract;
use GoDaddy\WordPress\MWC\Core\Auth\Sso\WordPress\Exceptions\UserCreateFailedException;
use GoDaddy\WordPress\MWC\Core\Auth\Sso\WordPress\Exceptions\UserNotFoundException;

/**
 * Care agent user association strategy, which finds a designated "Care Agent" WordPress account.
 */
class CareAgentUserAssociationStrategy extends AbstractUserAssociationStrategy implements CanCreateUserContract
{
    /**
     * Gets the designated Care Agent account.
     *
     * @return User
     * @throws UserNotFoundException
     */
    public function getLocalUser() : User
    {
        return CareUserService::getNewInstance()->getCareUserAccount();
    }

    /**
     * Creates the Care Agent user account.
     *
     * @return User
     * @throws UserCreateFailedException
     */
    public function createUser() : User
    {
        return CareUserService::getNewInstance()->getOrCreateCareUserAccount();
    }
}
