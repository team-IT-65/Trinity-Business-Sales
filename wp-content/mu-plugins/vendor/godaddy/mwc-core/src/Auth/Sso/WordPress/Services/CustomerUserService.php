<?php

namespace GoDaddy\WordPress\MWC\Core\Auth\Sso\WordPress\Services;

use GoDaddy\WordPress\MWC\Common\DataSources\WordPress\Adapters\UserAdapter;
use GoDaddy\WordPress\MWC\Common\Helpers\ArrayHelper;
use GoDaddy\WordPress\MWC\Common\Helpers\SanitizationHelper;
use GoDaddy\WordPress\MWC\Common\Models\User;
use GoDaddy\WordPress\MWC\Common\Platforms\Exceptions\PlatformRepositoryException;
use GoDaddy\WordPress\MWC\Common\Platforms\PlatformRepositoryFactory;
use GoDaddy\WordPress\MWC\Common\Traits\CanGetNewInstanceTrait;
use GoDaddy\WordPress\MWC\Core\Auth\Sso\WordPress\Traits\CanGetWpUserQueryTrait;
use WP_User;

class CustomerUserService
{
    use CanGetNewInstanceTrait;
    use CanGetWpUserQueryTrait;

    public const SSO_CUSTOMER_ID_META_KEY = '_gd_sso_customer_id';

    /**
     * Gets the local {@see User}  associated with the provided GoDaddy customer ID.
     *
     * @param string $customerId
     * @return ?User
     */
    public function getUserByCustomerId(string $customerId) : ?User
    {
        $users = $this->getWpUser($customerId);

        $user = ArrayHelper::get($users, '0');

        if ($user instanceof WP_User) {
            return User::seed((UserAdapter::getNewInstance($user))->convertFromSource());
        }

        return null;
    }

    /**
     * Get the WP_User that has metadata matching the GD customer ID.
     *
     * @param string $customerId
     * @return array<mixed>
     */
    protected function getWpUser(string $customerId) : array
    {
        return $this->getWpUserQuery([
            'meta_key'    => static::SSO_CUSTOMER_ID_META_KEY,
            'meta_value'  => $customerId,
            'count_total' => false,
            'number'      => 1,
        ])->get_results();
    }

    /**
     * Gets the {@see User} by WordPress username.
     *
     * @param string $username
     * @return User|null
     */
    public function getUserByHandle(string $username) : ?User
    {
        return User::getByHandle(SanitizationHelper::username($username));
    }

    /**
     * Gets a {@see User} that is eligible to be the customer user for the SSO feature.
     *
     * A user is eligible if it is the only non-Care administrator registered in the database.
     *
     * @return User|null
     */
    public function getEligibleAdministratorUser() : ?User
    {
        $handles = $this->getAdministratorUserHandles();

        // a fresh site should have only one non-Care administrator user, if we have more than one is not safe
        // to pick one as the customer user.
        if (count($handles) !== 1) {
            return null;
        }

        return $this->getUserByHandle($handles[0] ?? '');
    }

    /**
     * Gets up to two usernames from administrator users.
     *
     * @return string[]
     */
    protected function getAdministratorUserHandles() : array
    {
        return get_users([
            'role'       => 'administrator',
            'meta_query' => [
                [
                    'key'     => CareUserService::CARE_USER_META_KEY, // excluded in case a Care agent logs in before the site owner
                    'compare' => 'NOT EXISTS',
                ],
            ],
            'fields' => 'user_login',
            'number' => 2,
        ]);
    }

    /**
     * Get the main admin user based on the GoDaddy customer ID.
     *
     * @return ?User
     */
    public function getMainAdministratorUser() : ?User
    {
        try {
            $goDaddyCustomerId = PlatformRepositoryFactory::getNewInstance()->getPlatformRepository()->getGoDaddyCustomerId();
        } catch (PlatformRepositoryException $e) {
            return null;
        }

        if (empty($goDaddyCustomerId)) {
            return null;
        }

        return $this->getUserByCustomerId($goDaddyCustomerId);
    }

    /**
     * Gets the first administrator user that is not a care user.
     *
     * @return ?User
     */
    public function getOneAdministratorUser() : ?User
    {
        $adminUsers = $this->getAdministratorUserHandles();

        if (count($adminUsers) === 0) {
            return null;
        }

        return $this->getUserByHandle($adminUsers[0]);
    }
}
