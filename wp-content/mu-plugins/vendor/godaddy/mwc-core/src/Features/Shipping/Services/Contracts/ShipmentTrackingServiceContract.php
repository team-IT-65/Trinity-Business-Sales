<?php

namespace GoDaddy\WordPress\MWC\Core\Features\Shipping\Services\Contracts;

use Exception;
use GoDaddy\WordPress\MWC\Common\Exceptions\BaseException;
use GoDaddy\WordPress\MWC\Dashboard\Exceptions\OrderNotFoundException;
use GoDaddy\WordPress\MWC\Dashboard\Exceptions\ShipmentNotFoundException;
use GoDaddy\WordPress\MWC\Dashboard\Exceptions\ShipmentValidationFailedException;
use GoDaddy\WordPress\MWC\Shipping\Contracts\ShipmentContract;

interface ShipmentTrackingServiceContract
{
    /**
     * Gets an instance of the service for the Order identified with the given order ID.
     *
     * @param int $orderId
     *
     * @return static
     * @throws OrderNotFoundException
     */
    public static function for(int $orderId);

    /**
     * @param string $shipmentId
     *
     * @return ShipmentContract|null
     * @throws ShipmentNotFoundException
     */
    public function getShipment(string $shipmentId) : ?ShipmentContract;

    /**
     * @param ShipmentContract ...$shipments
     *
     * @return $this
     * @throws ShipmentValidationFailedException
     */
    public function addShipments(ShipmentContract ...$shipments);

    /**
     * @param string           $shipmentId
     * @param ShipmentContract $shipment
     *
     * @return $this
     * @throws ShipmentNotFoundException
     * @throws BaseException
     */
    public function updateShipment(string $shipmentId, ShipmentContract $shipment);

    /**
     * @param string $shipmentId
     *
     * @return $this
     * @throws ShipmentNotFoundException
     * @throws BaseException
     */
    public function deleteShipment(string $shipmentId);

    /**
     * @return $this
     * @throws Exception
     */
    public function updateFulfillmentStatus();
}
