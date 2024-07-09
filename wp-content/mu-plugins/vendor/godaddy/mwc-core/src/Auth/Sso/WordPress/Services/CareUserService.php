<?php

namespace GoDaddy\WordPress\MWC\Core\Auth\Sso\WordPress\Services;

use Exception;
use GoDaddy\WordPress\MWC\Common\Configuration\Configuration;
use GoDaddy\WordPress\MWC\Common\DataSources\WordPress\Adapters\UserAdapter;
use GoDaddy\WordPress\MWC\Common\Helpers\ArrayHelper;
use GoDaddy\WordPress\MWC\Common\Helpers\TypeHelper;
use GoDaddy\WordPress\MWC\Common\Models\User;
use GoDaddy\WordPress\MWC\Common\Traits\CanGetNewInstanceTrait;
use GoDaddy\WordPress\MWC\Core\Auth\Sso\WordPress\Exceptions\UserCreateFailedException;
use GoDaddy\WordPress\MWC\Core\Auth\Sso\WordPress\Exceptions\UserNotFoundException;
use GoDaddy\WordPress\MWC\Core\Auth\Sso\WordPress\Services\Contracts\CareUserServiceContract;
use GoDaddy\WordPress\MWC\Core\Auth\Sso\WordPress\Traits\CanGetWpUserQueryTrait;
use WP_Error;
use WP_User;

/**
 * Care user service.
 */
class CareUserService implements CareUserServiceContract
{
    use CanGetNewInstanceTrait;
    use CanGetWpUserQueryTrait;

    /** @var string care user meta key */
    public const CARE_USER_META_KEY = '_gd_care_account';

    /**
     * {@inheritDoc}
     */
    public function getCareUserAccount() : User
    {
        $wpUser = $this->locateCareUserAccount();

        if (! $wpUser) {
            throw new UserNotFoundException('This site does not have a care user account.');
        }

        return $this->convertUserFromSource($wpUser);
    }

    /**
     * {@inheritDoc}
     */
    public function getOrCreateCareUserAccount() : User
    {
        try {
            return $this->getCareUserAccount();
        } catch(UserNotFoundException $e) {
            return $this->createUser();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function isCareUserAccount(int $id) : bool
    {
        if ($id <= 0) {
            return false;
        }

        $metaField = get_user_meta($id, static::CARE_USER_META_KEY, true);

        return (bool) $metaField;
    }

    /**
     * Converts a WordPress {@see WP_User} into a care {@see User} object.
     *
     * @param WP_User $user
     * @return User
     */
    protected function convertUserFromSource(WP_User $user) : User
    {
        return User::seed(UserAdapter::getNewInstance($user)->convertFromSource());
    }

    /**
     * Gets the WordPress {@see WP_User} for the care user.
     *
     * @return WP_User|null
     */
    protected function locateCareUserAccount() : ?WP_User
    {
        $results = $this->getWpUserQuery([
            'number'          => 1,
            'calculate_total' => false,
            'meta_query'      => [
                [
                    'key'     => static::CARE_USER_META_KEY,
                    'compare' => 'EXISTS',
                ],
            ],
        ])->get_results();

        $wpUser = ArrayHelper::get($results, 0);

        return $wpUser instanceof WP_User ? $wpUser : null;
    }

    /**
     * Generates a username for the care user.
     *
     * @return string
     */
    public function generateCareAgentUsername() : string
    {
        return TypeHelper::string(Configuration::get('features.wordpress_sso.care.usernamePrefix'), '').'-'.wp_generate_password(16, false);
    }

    /**
     * Creates a new care agent {@see User}.
     *
     * @return User
     * @throws UserCreateFailedException
     */
    protected function createUser() : User
    {
        $userEmail = TypeHelper::stringOrNull(Configuration::get('features.wordpress_sso.care.email'));

        if (! $userEmail) {
            throw new UserCreateFailedException('Unable to create a care agent user account. No email address provided in configuration.');
        }

        $userName = TypeHelper::stringOrNull(Configuration::get('features.wordpress_sso.care.name'));

        if (! $userName) {
            throw new UserCreateFailedException('Unable to create a care agent user account. No user name provided in configuration.');
        }

        try {
            return $this->insertCareAgentUser($userName, $userEmail);
        } catch (Exception $exception) {
            throw new UserCreateFailedException(sprintf('Unable to create a care agent user account. %s', $exception->getMessage()), $exception);
        }
    }

    /**
     * Creates a new care agent {@see User}.
     *
     * @NOTE this method should be removed once the {@see User} model is able to handle roles and we have a proper UserMeta model available {unfulvio 2023-08-10}
     *
     * @param string $userName
     * @param string $userEmail
     * @return User
     * @throws UserNotFoundException|UserCreateFailedException
     */
    protected function insertCareAgentUser(string $userName, string $userEmail) : User
    {
        $userId = wp_insert_user([
            'user_login' => $this->generateCareAgentUsername(),
            'user_pass'  => wp_generate_password(32),
            'user_email' => $userEmail,
            'first_name' => $userName, // display_name defaults to first_name + last_name
            'role'       => 'administrator',
            'meta_input' => [
                static::CARE_USER_META_KEY => true,
            ],
        ]);

        if ($userId instanceof WP_Error) {
            throw new UserCreateFailedException(sprintf('Could not create user. %s', $userId->get_error_message()));
        }

        $user = User::get($userId);

        if (! $user) {
            throw new UserNotFoundException(sprintf('Could not find user after insert for ID %d.', $userId));
        }

        return $user;
    }
}
