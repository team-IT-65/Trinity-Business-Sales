<?php

namespace GoDaddy\WordPress\MWC\Core\Features\Shipping\Providers\ShipEngine;

use GoDaddy\WordPress\MWC\Common\Providers\AbstractProvider;
use GoDaddy\WordPress\MWC\Core\Features\Shipping\Contracts\ShippingProviderContract;
use GoDaddy\WordPress\MWC\Core\Features\Shipping\Providers\ShipEngine\Gateways\Carriers;
use GoDaddy\WordPress\MWC\Core\Features\Shipping\Providers\ShipEngine\Gateways\ShippingAccounts;
use GoDaddy\WordPress\MWC\Core\Features\Shipping\Providers\ShipEngine\Gateways\ShippingLabels;
use GoDaddy\WordPress\MWC\Core\Features\Shipping\Providers\ShipEngine\Gateways\ShippingRates;
use GoDaddy\WordPress\MWC\Core\Features\Shipping\Providers\ShipEngine\Gateways\ShippingTracking;
use GoDaddy\WordPress\MWC\Shipping\Traits\HasShippingAccountsGatewayTrait;
use GoDaddy\WordPress\MWC\Shipping\Traits\HasShippingCarriersGatewayTrait;
use GoDaddy\WordPress\MWC\Shipping\Traits\HasShippingLabelsTrait;
use GoDaddy\WordPress\MWC\Shipping\Traits\HasShippingRatesTrait;
use GoDaddy\WordPress\MWC\Shipping\Traits\HasShippingTrackingTrait;

class ShipEngineProvider extends AbstractProvider implements ShippingProviderContract
{
    use HasShippingAccountsGatewayTrait;
    use HasShippingCarriersGatewayTrait;
    use HasShippingLabelsTrait;
    use HasShippingRatesTrait;
    use HasShippingTrackingTrait;

    /** @var string the name for the shipping provider */
    protected $name = 'shipengine';

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->label = _x('ShipEngine', 'shipping provider name', 'mwc-core');
        $this->carriersGateway = Carriers::class;
        $this->accountsGateway = ShippingAccounts::class;
        $this->ratesGateway = ShippingRates::class;
        $this->trackingGateway = ShippingTracking::class;
        $this->labelsGateway = ShippingLabels::class;
    }
}
