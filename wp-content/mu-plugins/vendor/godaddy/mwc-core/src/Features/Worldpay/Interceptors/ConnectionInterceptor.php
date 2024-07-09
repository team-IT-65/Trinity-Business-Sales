<?php

namespace GoDaddy\WordPress\MWC\Core\Features\Worldpay\Interceptors;

use Exception;
use GoDaddy\WordPress\MWC\Common\Configuration\Configuration;
use GoDaddy\WordPress\MWC\Common\Events\Events;
use GoDaddy\WordPress\MWC\Common\Helpers\ArrayHelper;
use GoDaddy\WordPress\MWC\Common\Interceptors\AbstractInterceptor;
use GoDaddy\WordPress\MWC\Common\Register\Register;
use GoDaddy\WordPress\MWC\Common\Repositories\WooCommerceRepository;
use GoDaddy\WordPress\MWC\Core\Features\Worldpay\Exceptions\AccountUpdateFailedException;
use GoDaddy\WordPress\MWC\Core\Payments\Poynt;
use GoDaddy\WordPress\MWC\Core\Payments\Poynt\Events\AccountUpdatedEvent;
use GoDaddy\WordPress\MWC\Core\Payments\Poynt\Http\AbstractBusinessRequest;
use GoDaddy\WordPress\MWC\Core\Payments\Poynt\Http\PatchBusinessRequest;
use GoDaddy\WordPress\MWC\Core\Payments\Poynt\Http\StoreRequest;
use GoDaddy\WordPress\MWC\Core\Payments\Poynt\Onboarding;
use GoDaddy\WordPress\MWC\Core\WooCommerce\Payments\Events\Producers\OnboardingEventsProducer;
use WC_Payment_Gateway;

class ConnectionInterceptor extends AbstractInterceptor
{
    /** @var string */
    protected static $connectedFlagOptionName = 'mwc_payments_worldpay_connection_made';

    /** @var string[] */
    protected $autoConnectGateways = [
        'poynt',
        'godaddy-payments-payinperson',
        'godaddy-payments-apple-pay',
    ];

    /**
     * Adds the action & filter hooks.
     *
     * @throws Exception
     */
    public function addHooks() : void
    {
        Register::action()
            ->setGroup('admin_init')
            ->setHandler([$this, 'updateConnectionStatus'])
            ->execute();

        Register::action()
            ->setGroup(OnboardingEventsProducer::ACTION_UPDATE_ACCOUNT)
            ->setHandler([$this, 'updateAccount'])
            ->execute();
    }

    /**
     * Ensures the connection status is kept updated according to availability/validity of credentials.
     *
     * @throws Exception
     */
    public function updateConnectionStatus() : void
    {
        try {
            $isConnected = Poynt::getBusinessId()
                && Poynt::getAppId()
                && Poynt::getPublicKey()
                && Poynt::getPrivateKey();

            // ensure we can fetch the business via API, but only if all the other checks pass
            $isConnected && Poynt::getBusiness();
        } catch (Exception $e) {
            $isConnected = false;
        }

        Onboarding::setStatus($isConnected ? Onboarding::STATUS_CONNECTED : Onboarding::STATUS_DEACTIVATED);

        if (! static::wasConnected() && $isConnected) {
            $this->handleConnected();
        }
    }

    /**
     * Handles any tasks needed after connection is established.
     */
    protected function handleConnected() : void
    {
        static::setConnected(true);

        $this->maybeAutoEnablePaymentGateways();

        $this->updateAccount();
    }

    /**
     * Auto-Enables the configured payment gateways.
     */
    protected function maybeAutoEnablePaymentGateways() : void
    {
        if (! $gateways = $this->getWooCommerceGateways()) {
            return;
        }

        foreach ($this->autoConnectGateways as $gatewayId) {
            if ($gateway = ArrayHelper::get($gateways, $gatewayId)) {
                $gateway->update_option('enabled', 'yes');
            }
        }
    }

    /**
     * Updates the connected account.
     */
    public function updateAccount() : void
    {
        try {
            Onboarding::generateIds();

            $this->updateStore();
            $this->updateDevices();
            $this->updateBusiness();
        } catch (Exception $exception) {
            // the exceptions report to sentry themselves
        }

        // always broadcast the updated event
        Events::broadcast(new AccountUpdatedEvent());
    }

    /**
     * Updates the connected business.
     *
     * @throws Exception
     */
    protected function updateBusiness() : void
    {
        $this->updateAccountResource(PatchBusinessRequest::getNewInstance());
    }

    /**
     * Updates the connected store.
     *
     * @throws Exception
     */
    protected function updateStore() : void
    {
        $this->updateAccountResource(StoreRequest::getNewInstance(Poynt::getSiteStoreId())->setMethod('PATCH'));
    }

    /**
     * Updates an account resource based on the given request.
     *
     * @param AbstractBusinessRequest $request
     *
     * @throws Exception
     */
    protected function updateAccountResource(AbstractBusinessRequest $request) : void
    {
        $response = $request->setBody([
            [
                'op'    => 'add',
                'path'  => '/attributes/godaddyServiceType',
                'value' => Configuration::get('payments.poynt.serviceType'),
            ],
            [
                'op'    => 'add',
                'path'  => '/attributes/godaddyServiceId',
                'value' => Poynt::getServiceId(),
            ],
        ])
            ->send();

        if ($response->isError()) {
            throw new AccountUpdateFailedException($response->getErrorMessage() ?? 'Unknown error');
        }
    }

    /**
     * Updates the connected account device data.
     *
     * @throws Exception
     */
    protected function updateDevices() : void
    {
        $devices = Poynt::getStoreDevices();

        // check for activated Poynt smart terminal devices
        Poynt::checkActivatedDevices($devices);

        // get store ID from devices and save it
        Poynt::setStoreId($devices);
    }

    /**
     * Gets the WooCommerce gateways.
     *
     * @return array<string, WC_Payment_Gateway>|null
     */
    protected function getWooCommerceGateways() : ?array
    {
        if (! $woocommerce = WooCommerceRepository::getInstance()) {
            return null;
        }

        return $woocommerce->payment_gateways()->payment_gateways();
    }

    /**
     * Determines if the connection was already made.
     *
     * @return bool
     */
    public static function wasConnected() : bool
    {
        return wc_string_to_bool(get_option(static::$connectedFlagOptionName));
    }

    /**
     * Sets whether the connection was made.
     *
     * @param bool $wasConnected
     */
    public static function setConnected(bool $wasConnected) : void
    {
        update_option(static::$connectedFlagOptionName, wc_bool_to_string($wasConnected));
    }
}
