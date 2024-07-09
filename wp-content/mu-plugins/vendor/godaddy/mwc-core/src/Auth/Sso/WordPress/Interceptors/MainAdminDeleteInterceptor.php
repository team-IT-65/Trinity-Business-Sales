<?php

namespace GoDaddy\WordPress\MWC\Core\Auth\Sso\WordPress\Interceptors;

use Exception;
use GoDaddy\WordPress\MWC\Common\Exceptions\BaseException;
use GoDaddy\WordPress\MWC\Common\Exceptions\SentryException;
use GoDaddy\WordPress\MWC\Common\Helpers\ArrayHelper;
use GoDaddy\WordPress\MWC\Common\Helpers\TypeHelper;
use GoDaddy\WordPress\MWC\Common\Interceptors\AbstractInterceptor;
use GoDaddy\WordPress\MWC\Common\Register\Register;
use GoDaddy\WordPress\MWC\Core\Auth\Sso\WordPress\Services\CustomerUserService;
use GoDaddy\WordPress\MWC\Core\Auth\Sso\WordPress\Services\DefaultUserAssociationStrategy;
use WP_User;

/**
 * An interceptor that prevents the "main" admin user from being deleted via the wp-admin UI.
 *
 * The "main" admin user is the WordPress user that has the GoDaddy customer ID associated
 * with this site attached to their user meta.
 */
class MainAdminDeleteInterceptor extends AbstractInterceptor
{
    /**
     * Registers hooks for the interceptor.
     *
     * @return void
     * @throws Exception
     */
    public function addHooks() : void
    {
        Register::filter()
            ->setGroup('user_has_cap')
            ->setHandler([$this, 'maybeRemoveDeleteUserCapability'])
            ->setArgumentsCount(4)
            ->setPriority(PHP_INT_MAX)
            ->execute();

        Register::filter()
            ->setGroup('user_row_actions')
            ->setHandler([$this, 'maybeAddDeleteNoticeRowAction'])
            ->setArgumentsCount(2)
            ->execute();
    }

    /**
     * Returns true if the filter is relevant to this interceptor's action.
     *
     * @param string $requestedCapability
     * @param int $userIdToDelete
     * @param array<string, bool> $allCaps
     * @return bool
     */
    protected function isValidFilter(string $requestedCapability, int $userIdToDelete, array $allCaps) : bool
    {
        return 'delete_user' === $requestedCapability &&
            ! empty($userIdToDelete) &&
            ! empty($allCaps['delete_users']);
    }

    /**
     * Returns true if the user is the "main" admin user.
     *
     * @param int $userId
     * @return bool
     */
    protected function isMainAdminUser(int $userId) : bool
    {
        $user = CustomerUserService::getNewInstance()->getMainAdministratorUser();

        return $user && $user->getId() === $userId;
    }

    /**
     * Fetches the GoDaddy Customer ID mapped to the specified user.
     *
     * @param int $userId
     * @return mixed
     */
    protected function getMappedGoDaddyCustomerId(int $userId)
    {
        return get_user_meta($userId, DefaultUserAssociationStrategy::SSO_CUSTOMER_ID_META_KEY, true) ?: null;
    }

    /**
     * Maybe removes the delete_user capability if attempting to delete the main admin user.
     *
     * @param array<string, bool> $allCaps
     * @param string[] $caps
     * @param array<mixed> $args
     * @param WP_User $user
     * @return array<string, bool> The filtered capabilities.
     */
    public function maybeRemoveDeleteUserCapability($allCaps, $caps, $args, $user)
    {
        $allCaps = ArrayHelper::wrap($allCaps);
        $requestedCapability = TypeHelper::string(ArrayHelper::get($args, 0), '');

        $userIdToDelete = TypeHelper::int(ArrayHelper::get($args, 2), 0);

        if (! $this->isValidFilter($requestedCapability, $userIdToDelete, $allCaps)) {
            return $allCaps;
        }

        if ($this->isMainAdminUser($userIdToDelete)) {
            $allCaps['delete_users'] = false;
        }

        return $allCaps;
    }

    /**
     * Adds a row action to the main admin indicating that it cannot be deleted.
     *
     * @internal
     *
     * @param array<string, string>|mixed $actions
     * @param WP_User|mixed $user
     * @return array<string, string>|mixed
     */
    public function maybeAddDeleteNoticeRowAction($actions, $user)
    {
        if (! ArrayHelper::accessible($actions) || ! $user instanceof WP_User) {
            return $actions;
        }

        if ($this->shouldDisplayCannotDeleteNotice(TypeHelper::int($user->ID, 0))) {
            try {
                $actions = ArrayHelper::insertAfterKey($actions, ['gd_delete_notice' => $this->getCannotDeleteAdminNotice()], 'edit');
            } catch(BaseException $e) {
                SentryException::getNewInstance('Failed to add "cannot delete main admin" notice to user row actions.', $e);
            }
        }

        return $actions;
    }

    /**
     * Determines whether we should display the deletion notice {@see static::getCannotDeleteAdminNotice()}.
     *
     * @param int $userId
     * @return bool
     */
    protected function shouldDisplayCannotDeleteNotice(int $userId) : bool
    {
        // by default a user cannot delete themselves, so we do not need to show the notice for the current user
        return $userId !== get_current_user_id() && current_user_can('delete_users') && $this->isMainAdminUser($userId);
    }

    /**
     * Gets the notice that the main administrator account cannot be deleted.
     *
     * @return string
     */
    protected function getCannotDeleteAdminNotice() : string
    {
        $toolTipMessage = __('You cannot delete the main administrator account.', 'mwc-core');

        return '<span title="'.esc_attr($toolTipMessage).'">'.esc_html__('Delete', 'mwc-core').'</span>';
    }
}
