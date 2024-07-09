<?php

namespace GoDaddy\WordPress\MWC\Core\Features\Shipping\Interceptors;

use Exception;
use GoDaddy\WordPress\MWC\Common\Exceptions\SentryException;
use GoDaddy\WordPress\MWC\Common\Interceptors\AbstractInterceptor;
use GoDaddy\WordPress\MWC\Common\Register\Exceptions\InvalidActionException;
use GoDaddy\WordPress\MWC\Common\Register\Register;
use GoDaddy\WordPress\MWC\Core\Features\Shipping\API\Traits\CanGetShippingAccountServiceTrait;
use GoDaddy\WordPress\MWC\Core\Features\Shipping\Shipping;
use GoDaddy\WordPress\MWC\Shipping\Exceptions\Contracts\ShippingExceptionContract;

class DisconnectShippingAccountOnLocationChangeInterceptor extends AbstractInterceptor
{
    use CanGetShippingAccountServiceTrait;

    public static function shouldLoad() : bool
    {
        return ! Shipping::shouldLoad()
            && Shipping::loadedBefore()
            && Shipping::isAccountConnected()
            && ! Shipping::isBaseCountryEligible();
    }

    /**
     * Adds filters and actions to display a notice if the location changes from a supported region to an unsupported one.
     *
     * @throws InvalidActionException
     */
    public function addHooks() : void
    {
        $action = Register::action()
            ->setGroup('admin_init')
            ->setHandler([$this, 'disconnectShippingAccount']);

        /** @throws InvalidActionException {@see RegisterAction::execute()} really throws {@see InvalidActionException} instead of {@see Exception} */
        $action->execute();
    }

    /**
     * Uses the shipping account service to disable the account.
     *
     * @return void
     */
    public function disconnectShippingAccount() : void
    {
        try {
            $this->getShippingAccountService()->disconnectAccount($this->getShippingAccountService()->getAccount());
        } catch (ShippingExceptionContract $exception) {
            new SentryException('An error occurred trying to disconnect the shipping account.', $exception);
        }
    }
}
