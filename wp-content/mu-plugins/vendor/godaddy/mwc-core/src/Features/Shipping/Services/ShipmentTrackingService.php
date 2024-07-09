<?php

namespace GoDaddy\WordPress\MWC\Core\Features\Shipping\Services;

use Exception;
use GoDaddy\WordPress\MWC\Common\Configuration\Configuration;
use GoDaddy\WordPress\MWC\Common\Exceptions\BaseException;
use GoDaddy\WordPress\MWC\Core\Features\Shipping\Services\Contracts\ShipmentTrackingServiceContract;
use GoDaddy\WordPress\MWC\Dashboard\Exceptions\OrderNotFoundException;
use GoDaddy\WordPress\MWC\Dashboard\Exceptions\ShipmentNotFoundException;
use GoDaddy\WordPress\MWC\Dashboard\Exceptions\ShipmentValidationFailedException;
use GoDaddy\WordPress\MWC\Dashboard\Shipping\DataStores\ShipmentTracking\OrderFulfillmentDataStore;
use GoDaddy\WordPress\MWC\Dashboard\Shipping\Fulfillment;
use GoDaddy\WordPress\MWC\Shipping\Contracts\ShipmentContract;
use GoDaddy\WordPress\MWC\Shipping\Models\Orders\OrderFulfillment;

class ShipmentTrackingService implements ShipmentTrackingServiceContract
{
    /** @var OrderFulfillment */
    protected $orderFulfillment;

    final public function __construct(OrderFulfillment $orderFulfillment)
    {
        $this->orderFulfillment = $orderFulfillment;
    }

    /** {@inheritDoc} */
    public static function for(int $orderId)
    {
        return new static(static::getOrderFulfillment($orderId));
    }

    /**
     * Gets an OrderFulfillment object with the given order id.
     *
     * @param int $orderId
     *
     * @return OrderFulfillment
     *
     * @throws OrderNotFoundException
     */
    protected static function getOrderFulfillment(int $orderId) : OrderFulfillment
    {
        try {
            $fulfillment = static::getOrderFulfillmentDataStore()->read($orderId);
        } catch (Exception $exception) {
            throw new OrderNotFoundException("Order not found with ID {$orderId}.");
        }

        if (null === $fulfillment) {
            throw new OrderNotFoundException("Order not found with ID {$orderId}.");
        }

        return $fulfillment;
    }

    /**
     * Returns an instance of OrderFulfillmentDataStore.
     *
     * @return OrderFulfillmentDataStore
     */
    protected static function getOrderFulfillmentDataStore() : OrderFulfillmentDataStore
    {
        $store = Configuration::get('shipping.orderFulfillment.dataStore');

        return new $store;
    }

    /** {@inheritDoc} */
    public function getShipment(string $shipmentId) : ?ShipmentContract
    {
        $shipment = $this->orderFulfillment->getShipment($shipmentId);

        if (! $shipment) {
            throw new ShipmentNotFoundException("Shipment not found with ID {$shipmentId}.");
        }

        return $shipment;
    }

    /** {@inheritDoc} */
    public function addShipments(ShipmentContract ...$shipments)
    {
        try {
            Fulfillment::getInstance()->addShipments($this->orderFulfillment, $shipments);
        } catch (BaseException $exception) {
            throw new ShipmentValidationFailedException($exception->getMessage());
        }

        return $this;
    }

    /** {@inheritDoc} */
    public function updateShipment(string $shipmentId, ShipmentContract $shipment)
    {
        Fulfillment::getInstance()->updateShipment($this->orderFulfillment, $shipmentId, $shipment);

        return $this;
    }

    /** {@inheritDoc} */
    public function deleteShipment(string $shipmentId)
    {
        Fulfillment::getInstance()->deleteShipment($this->orderFulfillment, $shipmentId);

        return $this;
    }

    /** {@inheritDoc} */
    public function updateFulfillmentStatus()
    {
        Fulfillment::getInstance()->update($this->orderFulfillment);

        return $this;
    }
}
