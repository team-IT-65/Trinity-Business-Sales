<?php

namespace GoDaddy\WordPress\MWC\Core\Analytics\Providers\Contracts;

/**
 * Google Analytics provider contract.
 */
interface GoogleAnalyticsProviderContract extends AnalyticsProviderContract
{
    /**
     * Gets the tracking ID.
     *
     * @return string|null
     */
    public function getTrackingId() : ?string;

    /**
     * Gets the conversion label.
     *
     * @return string|null
     */
    public function getConversionLabel() : ?string;

    /**
     * Gets the developer ID.
     *
     * @return string|null
     */
    public function getDeveloperId() : ?string;
}
