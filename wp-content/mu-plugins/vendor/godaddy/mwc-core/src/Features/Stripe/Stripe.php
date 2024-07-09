<?php

namespace GoDaddy\WordPress\MWC\Core\Features\Stripe;

use GoDaddy\WordPress\MWC\Common\Features\AbstractFeature;

/**
 * The Stripe feature loader.
 */
class Stripe extends AbstractFeature
{
    /**
     * Gets the feature name, matching the key used in configuration.
     *
     * @return string
     */
    public static function getName() : string
    {
        return 'stripe';
    }

    /**
     * Initializes this feature.
     */
    public function load()
    {
    }
}
