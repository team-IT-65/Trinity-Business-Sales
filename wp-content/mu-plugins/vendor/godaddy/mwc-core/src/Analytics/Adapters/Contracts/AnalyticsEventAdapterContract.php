<?php

namespace GoDaddy\WordPress\MWC\Core\Analytics\Adapters\Contracts;

use GoDaddy\WordPress\MWC\Common\DataSources\Contracts\DataSourceAdapterContract;
use GoDaddy\WordPress\MWC\Core\Analytics\DataObjects\EventData;

/**
 * Contract for all analytics event adapters.
 */
interface AnalyticsEventAdapterContract extends DataSourceAdapterContract
{
    /**
     * Converts from data source format into an EventData object.
     *
     * @return EventData
     */
    public function convertFromSource() : EventData;
}
