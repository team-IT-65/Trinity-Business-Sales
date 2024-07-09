<?php

namespace GoDaddy\WordPress\MWC\Core\Auth\Sso\WordPress\Services\Contracts;

use GoDaddy\WordPress\MWC\Common\Models\User;
use GoDaddy\WordPress\MWC\Core\Auth\Sso\WordPress\Exceptions\UserHasNoValidIdException;
use GoDaddy\WordPress\MWC\Core\Auth\Sso\WordPress\Exceptions\UserNotFoundException;

/**
 * Contract for identifying and retrieving an associated {@see User}.
 */
interface UserAssociationStrategyContract
{
    /**
     * Gets the local user.
     *
     * @return User
     * @throws UserNotFoundException|UserHasNoValidIdException
     */
    public function getLocalUser() : User;
}
