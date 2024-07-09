<?php

namespace GoDaddy\WordPress\MWC\Core\Features\Shipping\Providers\ShipEngine\Gateways;

use GoDaddy\WordPress\MWC\Core\Features\Shipping\Contracts\ShippingRatesGatewayContract;
use GoDaddy\WordPress\MWC\Core\Features\Shipping\Providers\ShipEngine\Adapters\CalculateShippingRatesRequestAdapter;
use GoDaddy\WordPress\MWC\Core\Features\Shipping\Providers\ShipEngine\Adapters\GetShippingRateRequestAdapter;
use GoDaddy\WordPress\MWC\Shipping\Gateways\AbstractGateway;
use GoDaddy\WordPress\MWC\Shipping\Traits\CanCalculateShippingRatesTrait;
use GoDaddy\WordPress\MWC\Shipping\Traits\CanGetShippingRatesTrait;

class ShippingRates extends AbstractGateway implements ShippingRatesGatewayContract
{
    use CanGetShippingRatesTrait;
    use CanCalculateShippingRatesTrait;

    public function __construct()
    {
        $this->getShippingRateRequestAdapter = GetShippingRateRequestAdapter::class;
        $this->calculateShippingRatesRequestAdapter = CalculateShippingRatesRequestAdapter::class;
    }
}
