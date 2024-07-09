<?php

namespace GoDaddy\WordPress\MWC\Core\Features\Shipping\Providers\ShipEngine\Gateways;

use GoDaddy\WordPress\MWC\Core\Features\Shipping\Contracts\ShippingTrackingGatewayContract;
use GoDaddy\WordPress\MWC\Core\Features\Shipping\Providers\ShipEngine\Adapters\TrackingStatusRequestAdapter;
use GoDaddy\WordPress\MWC\Shipping\Gateways\AbstractGateway;
use GoDaddy\WordPress\MWC\Shipping\Traits\CanGetTrackingStatusTrait;

class ShippingTracking extends AbstractGateway implements ShippingTrackingGatewayContract
{
    use CanGetTrackingStatusTrait;

    public function __construct()
    {
        $this->trackingStatusRequestAdapter = TrackingStatusRequestAdapter::class;
    }
}
