<?php

namespace GoDaddy\WordPress\MWC\Core\Features\Shipping\API;

use GoDaddy\WordPress\MWC\Common\API\API as CommonAPI;
use GoDaddy\WordPress\MWC\Common\Components\Contracts\ComponentContract;
use GoDaddy\WordPress\MWC\Core\Features\Shipping\API\Controllers\AccountController;
use GoDaddy\WordPress\MWC\Core\Features\Shipping\API\Controllers\Orders\Shipments\ShippingLabelsController;
use GoDaddy\WordPress\MWC\Core\Features\Shipping\API\Controllers\Orders\ShippingRatesController;

class API extends CommonAPI
{
    /** @var class-string<ComponentContract>[] alphabetically ordered list of components to load */
    protected $componentClasses = [
        AccountController::class,
        ShippingRatesController::class,
        ShippingLabelsController::class,
    ];
}
