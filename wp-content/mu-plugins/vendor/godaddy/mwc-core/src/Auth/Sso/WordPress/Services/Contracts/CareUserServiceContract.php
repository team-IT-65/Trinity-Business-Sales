<?php

namespace GoDaddy\WordPress\MWC\Core\Auth\Sso\WordPress\Services\Contracts;

use GoDaddy\WordPress\MWC\Common\Models\User;
use GoDaddy\WordPress\MWC\Core\Auth\Sso\WordPress\Exceptions\UserCreateFailedException;
use GoDaddy\WordPress\MWC\Core\Auth\Sso\WordPress\Exceptions\UserNotFoundException;

/**
 * Service contract for the care user.
 */
interface CareUserServiceContract
{
    /**
     * Gets the user account for the care user.
     *
     * @return User
     * @throws UserNotFoundException
     */
    public function getCareUserAccount() : User;

    /**
     * Gets the user, or creates the user account if the user does not exist.
     *
     * @return User
     * @throws UserCreateFailedException
     */
    public function getOrCreateCareUserAccount() : User;

    /**
     * Returns true if the given user ID is the care user account.
     *
     * @param int $id
     * @return bool
     */
    public function isCareUserAccount(int $id) : bool;
}
