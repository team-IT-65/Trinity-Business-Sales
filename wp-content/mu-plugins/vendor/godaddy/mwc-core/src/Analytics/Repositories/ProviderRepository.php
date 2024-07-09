<?php

namespace GoDaddy\WordPress\MWC\Core\Analytics\Repositories;

use GoDaddy\WordPress\MWC\Common\Configuration\Configuration;
use GoDaddy\WordPress\MWC\Common\Helpers\TypeHelper;
use GoDaddy\WordPress\MWC\Core\Analytics\Providers\Contracts\AnalyticsProviderContract;

/**
 * Repository for analytics providers.
 */
class ProviderRepository
{
    /**
     * Gets available analytics providers.
     *
     * @param class-string<AnalyticsProviderContract> $providerContract
     * @return AnalyticsProviderContract[]
     */
    public static function getProviders(string $providerContract = AnalyticsProviderContract::class) : array
    {
        $providerClassNames = TypeHelper::arrayOfClassStrings(Configuration::get('analytics.providers', []), $providerContract, false);

        return array_map(function (string $providerClassName) {
            return new $providerClassName();
        }, $providerClassNames);
    }
}
