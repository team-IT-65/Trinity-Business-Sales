<?php

namespace GoDaddy\WordPress\MWC\Core\Features\Shipping;

use GoDaddy\WordPress\MWC\Common\Components\Contracts\ComponentContract;

class Lifecycle implements ComponentContract
{
    /** @var string */
    public const SHIPPING_PREVIOUSLY_LOADED_OPTION_NAME = 'mwc_shipping_previously_loaded';

    /** {@inheritDoc} */
    public function load() : void
    {
        $this->setShippingPreviouslyLoadedFlag();
    }

    /**
     * Sets an option flag to indicate that the shipping library was loaded.
     *
     * @return void
     */
    protected function setShippingPreviouslyLoadedFlag() : void
    {
        update_option(static::SHIPPING_PREVIOUSLY_LOADED_OPTION_NAME, 'yes');
    }
}
