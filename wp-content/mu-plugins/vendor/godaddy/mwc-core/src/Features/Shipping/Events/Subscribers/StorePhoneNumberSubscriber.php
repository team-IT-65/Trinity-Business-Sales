<?php

namespace GoDaddy\WordPress\MWC\Core\Features\Shipping\Events\Subscribers;

use GoDaddy\WordPress\MWC\Common\Events\Contracts\EventContract;
use GoDaddy\WordPress\MWC\Common\Events\Contracts\SubscriberContract;
use GoDaddy\WordPress\MWC\Common\Helpers\StringHelper;
use GoDaddy\WordPress\MWC\Core\Features\Shipping\Events\ShipmentQuoteEvent;
use GoDaddy\WordPress\MWC\Core\Features\Shipping\Interceptors\AddPhoneNumberToStoreAddressInterceptor;
use GoDaddy\WordPress\MWC\Shipping\Contracts\ShipmentContract;

class StorePhoneNumberSubscriber implements SubscriberContract
{
    /**
     * @param ShipmentQuoteEvent $event
     *
     * @return void
     */
    public function handle(EventContract $event)
    {
        if (! $this->isValidEvent($event)) {
            return;
        }

        /** @var ShipmentContract $shipment */
        $shipment = $event->getModel();

        $phoneNumber = $this->getPhoneNumberFromShipment($shipment);

        if ($this->shouldUpdatePhoneNumberField($phoneNumber)) {
            $this->updatePhoneNumberField($phoneNumber);
        }
    }

    /**
     * Retrieves the phone number from the given shipment.
     *
     * @param ShipmentContract $shipment The shipment from which the number should be retrieved.
     *
     * @return string The shipment, or an empty string if not-found.
     */
    protected function getPhoneNumberFromShipment(ShipmentContract $shipment) : string
    {
        $address = $shipment->getOriginAddress();

        return $address ? $address->getPhone() : '';
    }

    /**
     * @param string $phoneNumber
     *
     * @return bool
     */
    protected function shouldUpdatePhoneNumberField(string $phoneNumber) : bool
    {
        if (empty($phoneNumber)) {
            return false;
        }

        return ! $this->getStoredPhoneNumber() || $this->getStoredPhoneNumber() !== $phoneNumber;
    }

    /**
     * Validates the input event.
     *
     * @param EventContract $event The event to validate.
     *
     * @return bool
     */
    protected function isValidEvent(EventContract $event) : bool
    {
        return $event instanceof ShipmentQuoteEvent && $event->getModel() instanceof ShipmentContract;
    }

    /**
     * Retrieves the stored phone number from the database.
     *
     * @return string|null The phone number, or null if the phone number is not set.
     */
    protected function getStoredPhoneNumber() : ?string
    {
        $result = StringHelper::ensureScalar(
            get_option(AddPhoneNumberToStoreAddressInterceptor::PHONE_NUMBER_FIELD_NAME)
        );

        return ! empty($result) ? (string) $result : null;
    }

    /**
     * Stores the given phone number in the database.
     *
     * @param string $phoneNumber The phone number to store.
     *
     * @return bool True if saved successfully, otherwise false.
     */
    protected function updatePhoneNumberField(string $phoneNumber) : bool
    {
        return update_option(AddPhoneNumberToStoreAddressInterceptor::PHONE_NUMBER_FIELD_NAME, $phoneNumber);
    }
}
