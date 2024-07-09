<?php

namespace GoDaddy\WordPress\MWC\Core\Auth\Sso\WordPress\Interceptors;

use Exception;
use GoDaddy\WordPress\MWC\Common\Exceptions\SentryException;
use GoDaddy\WordPress\MWC\Common\Interceptors\AbstractInterceptor;
use GoDaddy\WordPress\MWC\Common\Models\User;
use GoDaddy\WordPress\MWC\Common\Register\Register;
use GoDaddy\WordPress\MWC\Core\Auth\Sso\WordPress\Events\Subscribers\ScheduleCareAgentDeleteSubscriber;
use GoDaddy\WordPress\MWC\Core\Auth\Sso\WordPress\Services\CustomerUserService;

/**
 * Intercepts a Care Agent user delete event.
 */
class UserDeleteInterceptor extends AbstractInterceptor
{
    protected CustomerUserService $customerUserService;

    /**
     * Constructor.
     *
     * @param CustomerUserService $customerUserService
     */
    public function __construct(CustomerUserService $customerUserService)
    {
        $this->customerUserService = $customerUserService;
    }

    /**
     * Add hooks.
     *
     * @return void
     * @throws Exception
     */
    public function addHooks() : void
    {
        Register::action()
            ->setGroup(ScheduleCareAgentDeleteSubscriber::USER_DELETE_ACTION_NAME)
            ->setPriority(PHP_INT_MAX)
            ->setHandler([$this, 'deleteUser'])
            ->setArgumentsCount(1)
            ->execute();
    }

    /**
     * Delete the user.
     *
     * @param mixed $userId The user ID.
     *
     * @return void
     */
    public function deleteUser($userId) : void
    {
        try {
            $reassignUser = $this->getReassignUser();
            $reassignUserId = $reassignUser ? $reassignUser->getId() : null;

            User::seed(['id' => $userId])->delete($reassignUserId);
        } catch (Exception $exception) {
            SentryException::getNewInstance('Unable to delete user.', $exception);
        }
    }

    /**
     * Gets the user to reassign the deleted user's content to.
     */
    public function getReassignUser() : ?User
    {
        return $this->customerUserService->getMainAdministratorUser() ?? $this->customerUserService->getOneAdministratorUser();
    }
}
