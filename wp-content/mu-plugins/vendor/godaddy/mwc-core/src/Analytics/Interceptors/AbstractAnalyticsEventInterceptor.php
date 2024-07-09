<?php

namespace GoDaddy\WordPress\MWC\Core\Analytics\Interceptors;

use Exception;
use GoDaddy\WordPress\MWC\Common\Configuration\Configuration;
use GoDaddy\WordPress\MWC\Common\Interceptors\AbstractInterceptor;
use GoDaddy\WordPress\MWC\Common\Register\Register;
use GoDaddy\WordPress\MWC\Common\Repositories\WooCommerceRepository;
use GoDaddy\WordPress\MWC\Core\Analytics\Providers\Contracts\AnalyticsProviderContract;
use GoDaddy\WordPress\MWC\Core\Analytics\Repositories\ProviderRepository;

/**
 * Abstract interceptor for WordPress and WooCommerce hooks related to analytics events.
 */
abstract class AbstractAnalyticsEventInterceptor extends AbstractInterceptor
{
    /** @var class-string<AnalyticsProviderContract> */
    protected static string $providerContractName = AnalyticsProviderContract::class;

    /**
     * Adds hooks.
     *
     * @return void
     * @throws Exception
     */
    public function addHooks() : void
    {
        Register::action()
            ->setGroup('wp_enqueue_scripts')
            ->setHandler([$this, 'maybeEnqueueJs'])
            ->execute();
    }

    /**
     * Determines if any analytics scripts should be enqueued.
     *
     * @return bool returns true when the analytics component is enabled, WooCommerce is active and at least one analytics provider is found
     */
    public static function shouldEnqueueJs() : bool
    {
        if (! Configuration::get('analytics.enabled')) {
            return false;
        }

        if (! WooCommerceRepository::isWooCommerceActive()) {
            return false;
        }

        foreach (ProviderRepository::getProviders(static::$providerContractName) as $provider) {
            if ($provider->isActive()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Maybe enqueues any analytics scripts.
     *
     * @internal
     *
     * @return void
     */
    public function maybeEnqueueJs() : void
    {
        if (! static::shouldEnqueueJs()) {
            return;
        }

        $this->enqueueJs();
    }

    /**
     * Enqueues any analytics scripts.
     *
     * @return void
     */
    abstract protected function enqueueJs() : void;
}
