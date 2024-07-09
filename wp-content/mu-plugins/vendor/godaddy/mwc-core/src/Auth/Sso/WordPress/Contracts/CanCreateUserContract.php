<?php

namespace GoDaddy\WordPress\MWC\Core\Auth\Sso\WordPress\Contracts;

use GoDaddy\WordPress\MWC\Common\Models\User;
use GoDaddy\WordPress\MWC\Core\Auth\Sso\WordPress\Exceptions\UserCreateFailedException;

/**
 * Declares that this class can create a user.
 */
interface CanCreateUserContract
{
    /**
     * Creates the user.
     *
     * @return User
     * @throws UserCreateFailedException
     */
    public function createUser() : User;
}
