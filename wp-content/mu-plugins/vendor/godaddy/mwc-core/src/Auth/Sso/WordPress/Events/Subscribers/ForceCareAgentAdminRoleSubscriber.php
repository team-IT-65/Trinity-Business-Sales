<?php

namespace GoDaddy\WordPress\MWC\Core\Auth\Sso\WordPress\Events\Subscribers;

use Exception;
use GoDaddy\WordPress\MWC\Common\Events\Contracts\EventContract;
use GoDaddy\WordPress\MWC\Common\Events\Contracts\SubscriberContract;
use GoDaddy\WordPress\MWC\Common\Exceptions\SentryException;
use GoDaddy\WordPress\MWC\Common\Helpers\ArrayHelper;
use GoDaddy\WordPress\MWC\Common\Helpers\TypeHelper;
use GoDaddy\WordPress\MWC\Core\Auth\Sso\WordPress\Events\CareUserLogInEvent;
use WP_User;

/**
 * Subscriber to the {@see CareUserLogInEvent} event.
 *
 * This subscriber will force the admin user role to be assigned to the Care Agent user.
 */
class ForceCareAgentAdminRoleSubscriber implements SubscriberContract
{
    /**
     * Determines if the user matching the given ID is an admin.
     *
     * @param int $userId
     * @return bool
     */
    protected function isUserAdministrator(int $userId) : bool
    {
        $wpUser = get_userdata($userId);

        if (! $wpUser instanceof WP_User) {
            SentryException::getNewInstance(sprintf('Unable to retrieve user roles for care agent user with ID %d', $userId));

            return false;
        }

        return ArrayHelper::contains(TypeHelper::array($wpUser->roles, []), 'administrator');
    }

    /**
     * Handles the event.
     *
     * @param EventContract|CareUserLogInEvent $event
     * @return void
     * @throws Exception
     */
    public function handle(EventContract $event)
    {
        if (! $event instanceof CareUserLogInEvent) {
            return;
        }

        $user = $event->getUser();
        $userId = $user->getId();

        if (! $userId) {
            SentryException::getNewInstance(sprintf('Could not get a valid user ID for the %s event.', get_class($event)));
        } elseif (! $this->isUserAdministrator($userId)) {
            $user->delete();

            wp_die(__('The care agent account on this site has been misconfigured. Please log in again to fix this issue.', 'mwc-core'));
        }
    }
}
