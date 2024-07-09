<?php

namespace GoDaddy\WordPress\MWC\Core\Features\Shipping\API\Traits;

use GoDaddy\WordPress\MWC\Core\Features\Shipping\Services\Contracts\ShippingAccountServiceContract;
use GoDaddy\WordPress\MWC\Core\Features\Shipping\Services\ShippingAccountService;

trait CanGetShippingAccountServiceTrait
{
    /** @var ?ShippingAccountServiceContract */
    protected $shippingAccountService;

    /**
     * Gets an instance of {@see ShippingAccountServiceContract}.
     *
     * @return ShippingAccountServiceContract
     */
    protected function getShippingAccountService() : ShippingAccountServiceContract
    {
        if (! $this->shippingAccountService) {
            $this->shippingAccountService = ShippingAccountService::getNewInstance();
        }

        return $this->shippingAccountService;
    }
}
