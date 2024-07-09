<?php

namespace GoDaddy\WordPress\MWC\Core\Features\Worldpay\Interceptors;

use Exception;
use GoDaddy\WordPress\MWC\Common\Enqueue\Enqueue;
use GoDaddy\WordPress\MWC\Common\Helpers\ArrayHelper;
use GoDaddy\WordPress\MWC\Common\Interceptors\AbstractInterceptor;
use GoDaddy\WordPress\MWC\Common\Register\Register;
use GoDaddy\WordPress\MWC\Common\Repositories\WordPressRepository;

class OverridesInterceptor extends AbstractInterceptor
{
    /**
     * Adds the action & filter hooks.
     *
     * @throws Exception
     */
    public function addHooks() : void
    {
        Register::action()
            ->setGroup('admin_enqueue_scripts')
            ->setHandler([$this, 'removeWooCommerceSetupMetaBox'])
            ->execute();

        Register::filter()
            ->setGroup('views_plugin-install')
            ->setHandler([$this, 'removeRecommendedTab'])
            ->execute();

        Register::filter()
            ->setGroup('woocommerce_payment_gateways')
            ->setHandler([$this, 'filterPaymentGateways'])
            ->execute();

        Register::filter()
            ->setGroup('woocommerce_com_integration_settings')
            ->setHandler([$this, 'filterWooCommerceDotComSettings'])
            ->execute();

        Register::filter()
            ->setGroup('pre_option_woocommerce_show_marketplace_suggestions')
            ->setHandler([$this, 'filterMarketplaceSuggestionsDefault'])
            ->execute();

        Register::filter()
            ->setGroup('woocommerce_allow_marketplace_suggestions')
            ->setHandler([$this, 'disallowMarketplaceSuggestions'])
            ->execute();

        Register::action()
                ->setGroup('admin_enqueue_scripts')
                ->setHandler([$this, 'enqueueAssets'])
                ->execute();
    }

    /**
     * Enqueues Worldpay assets on all admin pages.
     *
     * @throws Exception
     */
    public function enqueueAssets() : void
    {
        Enqueue::script()
               ->setHandle('gd-worldpay-overrides')
               ->setSource(WordPressRepository::getAssetsUrl('js/features/worldpay/overrides.js'))
               ->setDependencies(['jquery'])
               ->setDeferred(true)
               ->execute();
    }

    /**
     * Removes the WooCommerce Setup meta box from the admin.
     *
     * @internal
     */
    public function removeWooCommerceSetupMetaBox() : void
    {
        remove_meta_box(
            'wc_admin_dashboard_setup',
            'dashboard',
            'normal'
        );
    }

    /**
     * Removes the Recommended tab from views.
     *
     * @param mixed|array<string, string> $views current view list
     * @return mixed|array<string, string> modified view list
     * @internal
     */
    public function removeRecommendedTab($views)
    {
        if (ArrayHelper::accessible($views)) {
            unset($views['plugin-install-recommended']);
        }

        return $views;
    }

    /**
     * Filters the WC payment gateways.
     *
     * @param mixed|array<int, string> $gateways current gateways list
     * @return mixed|array<int, string> modified gateways list
     * @internal
     */
    public function filterPaymentGateways($gateways)
    {
        if (ArrayHelper::accessible($gateways)) {
            foreach (['WC_Gateway_BACS', 'WC_Gateway_Cheque', 'WC_Gateway_COD'] as $gateway) {
                unset($gateways[array_search($gateway, $gateways, true)]);
            }

            return array_values($gateways);
        }

        return $gateways;
    }

    /**
     * Filters settings in WooCommerce Settings > Advanced > WooCommerce.com.
     *
     * Removes the `woocommerce_show_marketplace_suggestions` setting.
     *
     * @internal
     *
     * @param array<string, mixed>|mixed $settings
     * @return array<string, mixed>|mixed
     */
    public function filterWooCommerceDotComSettings($settings)
    {
        if (! ArrayHelper::accessible($settings)) {
            return $settings;
        }

        foreach ($settings as $index => $settingGroup) {
            if (in_array(ArrayHelper::get($settingGroup, 'id'), ['marketplace_suggestions', 'woocommerce_show_marketplace_suggestions'], true)) {
                unset($settings[$index]);
            }
        }

        return $settings;
    }

    /**
     * Filters the default value of Woo's marketplace suggestions option.
     *
     * Always return "no" in the interceptor, which applies to Worldpay sites only.
     *
     * @return string
     */
    public function filterMarketplaceSuggestionsDefault() : string
    {
        return 'no';
    }

    /**
     * Disallow marketplace suggestions.
     *
     * @return bool
     */
    public function disallowMarketplaceSuggestions() : bool
    {
        return false;
    }
}
