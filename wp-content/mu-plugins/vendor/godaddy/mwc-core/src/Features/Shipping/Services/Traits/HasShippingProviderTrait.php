<?php

namespace GoDaddy\WordPress\MWC\Core\Features\Shipping\Services\Traits;

use GoDaddy\WordPress\MWC\Common\Configuration\Configuration;
use GoDaddy\WordPress\MWC\Core\Features\Shipping\Contracts\ShippingProviderContract;
use GoDaddy\WordPress\MWC\Shipping\Shipping;

trait HasShippingProviderTrait
{
    /** @var ShippingProviderContract */
    protected $shippingProvider;

    /**
     * Returns an instance of {@see ShippingProviderContract}.
     *
     * This method ignores the {@see Exception} instances thrown by HasProvidersTrait::provider()
     * on purpose because they are never really thrown inside Shipping::provider() method.
     *
     * @return ShippingProviderContract
     */
    protected static function getShippingProviderInstance() : ShippingProviderContract
    {
        $providerName = Configuration::get('shipping.labels.provider');

        /** @var ShippingProviderContract $provider */
        $provider = Shipping::provider($providerName); // @phpstan-ignore-line

        return $provider;
    }
}
