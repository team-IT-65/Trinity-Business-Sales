<?php

namespace GoDaddy\WordPress\MWC\Core\Analytics\Interceptors;

use Exception;
use GoDaddy\WordPress\MWC\Common\Configuration\Configuration;
use GoDaddy\WordPress\MWC\Common\Enqueue\Enqueue;
use GoDaddy\WordPress\MWC\Common\Helpers\TypeHelper;
use GoDaddy\WordPress\MWC\Common\Register\Register;
use GoDaddy\WordPress\MWC\Common\Repositories\WordPressRepository;
use GoDaddy\WordPress\MWC\Core\Analytics\Providers\Contracts\AnalyticsProviderContract;
use GoDaddy\WordPress\MWC\Core\Analytics\Providers\Contracts\GoogleAnalyticsProviderContract;
use GoDaddy\WordPress\MWC\Core\Analytics\Repositories\ProviderRepository;

/**
 * Google Analytics event interceptor class.
 */
class GoogleAnalyticsEventInterceptor extends AbstractAnalyticsEventInterceptor
{
    /** @var class-string<AnalyticsProviderContract> */
    protected static string $providerContractName = GoogleAnalyticsProviderContract::class;

    /**
     * Adds hooks.
     *
     * @throws Exception
     */
    public function addHooks() : void
    {
        /* @see AbstractAnalyticsEventInterceptor::maybeEnqueueJs() */
        parent::addHooks();

        Register::action()
            ->setGroup('wp_head')
            ->setHandler([$this, 'maybeOutputGTag'])
            ->execute();
    }

    /**
     * Maybe outputs a GTag script.
     *
     * @internal
     *
     * @return void
     */
    public function maybeOutputGTag() : void
    {
        foreach (ProviderRepository::getProviders() as $provider) {
            if ($provider instanceof GoogleAnalyticsProviderContract && $provider->isActive()) {
                $developerId = TypeHelper::string($provider->getDeveloperId(), '');
                $trackingId = TypeHelper::string($provider->getTrackingId(), '');

                if (empty($trackingId) || empty($developerId)) {
                    continue;
                } ?>
                <script async src='https://www.googletagmanager.com/gtag/js?id=<?php echo esc_attr($trackingId); ?>'></script>
                <script>
                    window.dataLayer = window.dataLayer || [];
                    function gtag(){dataLayer.push(arguments)}
                    gtag('js', new Date());
                    gtag('config', '<?php echo esc_js($trackingId); ?>');
                    gtag('set', 'developer_id.<?php echo esc_js($developerId); ?>', true);
                </script>
                <?php
            }
        }
    }

    /**
     * Determines whether the scripts should be enqueued by this class.
     *
     * @return bool
     */
    public static function shouldEnqueueJs() : bool
    {
        if (! parent::shouldEnqueueJs()) {
            return false;
        }

        return TypeHelper::bool(apply_filters('mwc_analytics_google_gtag_js', true), true);
    }

    /**
     * Enqueues the Google Analytics frontend script.
     *
     * @see AbstractAnalyticsEventInterceptor::maybeEnqueueJs() and constructor
     *
     * @return void
     * @throws Exception
     */
    protected function enqueueJs() : void
    {
        Enqueue::script()
            ->setHandle('gd-google-analytics')
            ->setSource(WordPressRepository::getAssetsUrl('js/analytics/frontend/google-analytics.js'))
            ->setVersion(TypeHelper::string(Configuration::get('mwc.version'), ''))
            ->setDependencies([ScriptEventDataInterceptor::SCRIPT_HANDLE])
            ->setDeferred(true)
            ->attachInlineScriptObject('gdAnalyticsGoogle')
            ->attachInlineScriptVariables($this->getInlineScriptVariables())
            ->execute();
    }

    /**
     * Gets the inline JavaScript variables.
     *
     * @return array<string, mixed>
     */
    protected function getInlineScriptVariables() : array
    {
        return [
            'providers' => $this->getProviderData(),
        ];
    }

    /**
     * Gets the tracking IDs and conversion labels for all active Google providers.
     *
     * @return array<int, array<string, string|null>>
     */
    protected function getProviderData() : array
    {
        $providerData = [];

        foreach (ProviderRepository::getProviders(static::$providerContractName) as $provider) {
            if ($provider instanceof GoogleAnalyticsProviderContract && $provider->isActive() && $trackingId = $provider->getTrackingId()) {
                $providerData[$trackingId] = [
                    'trackingId'      => $trackingId,
                    'conversionLabel' => $provider->getConversionLabel(),
                ];
            }
        }

        return array_values($providerData);
    }
}
