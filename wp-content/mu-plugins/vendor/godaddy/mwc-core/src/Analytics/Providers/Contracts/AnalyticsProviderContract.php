<?php

namespace GoDaddy\WordPress\MWC\Core\Analytics\Providers\Contracts;

/**
 * A contract for analytics / user behavior tracking providers.
 */
interface AnalyticsProviderContract
{
    /**
     * Determines whether the provider is active.
     *
     * @return bool
     */
    public function isActive() : bool;
}
