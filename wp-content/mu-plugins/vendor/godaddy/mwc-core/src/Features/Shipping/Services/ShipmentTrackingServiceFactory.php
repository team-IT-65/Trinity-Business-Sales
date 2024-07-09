<?php

namespace GoDaddy\WordPress\MWC\Core\Features\Shipping\Services;

use GoDaddy\WordPress\MWC\Common\Configuration\Configuration;
use GoDaddy\WordPress\MWC\Common\Traits\CanGetNewInstanceTrait;
use GoDaddy\WordPress\MWC\Core\Features\Shipping\Services\Contracts\ShipmentTrackingServiceContract;
use GoDaddy\WordPress\MWC\Dashboard\Exceptions\OrderNotFoundException;

class ShipmentTrackingServiceFactory
{
    use CanGetNewInstanceTrait;

    /**
     * Returns an instance of {@see ShipmentTrackingServiceContract}.
     *
     * @param int $orderId
     *
     * @return ShipmentTrackingServiceContract
     * @throws OrderNotFoundException
     */
    public function getShipmentTrackingService(int $orderId) : ShipmentTrackingServiceContract
    {
        /** @var class-string<ShipmentTrackingServiceContract> $service */
        $service = Configuration::get('shipping.labels.trackingService');

        return $service::for($orderId);
    }
}
