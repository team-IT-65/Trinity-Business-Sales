<?php

namespace GoDaddy\WordPress\MWC\Core\Features\Shipping\Providers\ShipEngine\Gateways;

use GoDaddy\WordPress\MWC\Core\Features\Shipping\Contracts\ShippingLabelsGatewayContract;
use GoDaddy\WordPress\MWC\Core\Features\Shipping\Providers\ShipEngine\Adapters\PurchaseShippingLabelsRequestAdapter;
use GoDaddy\WordPress\MWC\Core\Features\Shipping\Providers\ShipEngine\Adapters\VoidShippingLabelRequestAdapter;
use GoDaddy\WordPress\MWC\Shipping\Gateways\AbstractGateway;
use GoDaddy\WordPress\MWC\Shipping\Traits\CanPurchaseShippingLabelsTrait;
use GoDaddy\WordPress\MWC\Shipping\Traits\CanVoidShippingLabelsTrait;

class ShippingLabels extends AbstractGateway implements ShippingLabelsGatewayContract
{
    use CanPurchaseShippingLabelsTrait;
    use CanVoidShippingLabelsTrait;

    public function __construct()
    {
        $this->purchaseShippingLabelsRequestAdapter = PurchaseShippingLabelsRequestAdapter::class;
        $this->voidShippingLabelsRequestAdapter = VoidShippingLabelRequestAdapter::class;
    }
}
