<?php

namespace GoDaddy\WordPress\MWC\Core\Auth\Sso\WordPress\Services;

use GoDaddy\WordPress\MWC\Common\Helpers\TypeHelper;
use GoDaddy\WordPress\MWC\Common\Models\User;
use GoDaddy\WordPress\MWC\Core\Auth\Sso\WordPress\Exceptions\UserHasNoValidIdException;
use GoDaddy\WordPress\MWC\Core\Auth\Sso\WordPress\Exceptions\UserNotFoundException;

/**
 * Default user association strategy, which finds an account based on a customer ID or username match.
 */
class DefaultUserAssociationStrategy extends AbstractUserAssociationStrategy
{
    public const SSO_CUSTOMER_ID_META_KEY = '_gd_sso_customer_id';

    protected ?CustomerUserService $customerUserService;

    /**
     * Gets the local user associated with the given token.
     * We prefer to search by GoDaddy customer ID, but if that fails we fall back to matching based on username.
     *
     * @return User
     * @throws UserNotFoundException|UserHasNoValidIdException
     */
    public function getLocalUser() : User
    {
        $customerId = $this->token->getCustomerId();

        if ($user = $this->getCustomerUserService()->getUserByCustomerId($customerId)) {
            return $user;
        } elseif ($user = $this->getUserByHandleOrEligibleAdministrator($this->token->getUsername())) {
            // Save this association, so we can use customer ID next time we authenticate.
            $this->associateUserWithCustomerId($user, $customerId);

            return $user;
        } else {
            throw new UserNotFoundException('No local user found for the given token.');
        }
    }

    /**
     * Attempts to get the local user by username, or finding an eligible administrator user.
     */
    protected function getUserByHandleOrEligibleAdministrator(string $username) : ?User
    {
        $customerUserService = $this->getCustomerUserService();

        return $customerUserService->getUserByHandle($username) ?? $customerUserService->getEligibleAdministratorUser();
    }

    /**
     * Associates the {@see User} with the GoDaddy customer ID.
     *
     * @param User $user with ID
     * @param string $customerId
     * @return void
     * @throws UserHasNoValidIdException
     */
    protected function associateUserWithCustomerId(User $user, string $customerId) : void
    {
        $userId = TypeHelper::int($user->getId(), 0);

        if (! $userId) {
            throw new UserHasNoValidIdException('Cannot associate user with customer ID: user has no valid ID.');
        }

        update_user_meta($userId, static::SSO_CUSTOMER_ID_META_KEY, $customerId);
    }

    /**
     * Get the CustomerUserService instance.
     *
     * @return CustomerUserService
     */
    protected function getCustomerUserService() : CustomerUserService
    {
        return $this->customerUserService ??= CustomerUserService::getNewInstance();
    }
}
