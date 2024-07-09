<?php

namespace GoDaddy\WordPress\MWC\Core\Features\Shipping\Providers\ShipEngine\Gateways;

use GoDaddy\WordPress\MWC\Core\Features\Shipping\Contracts\ShippingCarriersGatewayContract;
use GoDaddy\WordPress\MWC\Core\Features\Shipping\Providers\ShipEngine\Adapters\ListCarriersRequestAdapter;
use GoDaddy\WordPress\MWC\Shipping\Gateways\AbstractGateway;
use GoDaddy\WordPress\MWC\Shipping\Traits\CanListShippingCarriersTrait;

class Carriers extends AbstractGateway implements ShippingCarriersGatewayContract
{
    use CanListShippingCarriersTrait;

    public function __construct()
    {
        $this->listCarriersRequestAdapter = ListCarriersRequestAdapter::class;
    }
}
