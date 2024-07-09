<?php

namespace GoDaddy\WordPress\MWC\Core\Features\Shipping\Interceptors;

use Exception;
use GoDaddy\WordPress\MWC\Common\Interceptors\AbstractInterceptor;
use GoDaddy\WordPress\MWC\Common\Models\User;
use GoDaddy\WordPress\MWC\Common\Register\Exceptions\InvalidActionException;
use GoDaddy\WordPress\MWC\Common\Register\Register;
use GoDaddy\WordPress\MWC\Common\Register\Types\RegisterAction;
use GoDaddy\WordPress\MWC\Core\Admin\Notices\Notices;
use GoDaddy\WordPress\MWC\Core\Features\Shipping\Notices\LocationChangeNotice;
use GoDaddy\WordPress\MWC\Core\Features\Shipping\Shipping;

class RestoreLocationChangeNoticeInterceptor extends AbstractInterceptor
{
    /** {@inheritDoc} */
    public static function shouldLoad() : bool
    {
        return Shipping::shouldLoad();
    }

    /**
     * Registers actions to restore the location change notice.
     *
     * @throws InvalidActionException
     */
    public function addHooks() : void
    {
        $action = Register::action()
                ->setGroup('admin_init')
                ->setHandler([$this, 'restoreLocationChangeNotice']);

        /** @throws InvalidActionException {@see RegisterAction::execute()} really throws {@see InvalidActionException} instead of {@see Exception} */
        $action->execute();
    }

    /**
     * Restores the notice that is shown when the store's location changes to an unsupported region.
     *
     * If the location ever changes to an unsupported region, we want to make sure that the notice is
     * not dismissed.
     *
     * @internal
     */
    public function restoreLocationChangeNotice() : void
    {
        if (! $user = User::getCurrent()) {
            return;
        }

        $notice = LocationChangeNotice::getNewInstance();

        if (! $notice->isDismissed()) {
            return;
        }

        Notices::restoreNotice($user, $notice->getId());
    }
}
