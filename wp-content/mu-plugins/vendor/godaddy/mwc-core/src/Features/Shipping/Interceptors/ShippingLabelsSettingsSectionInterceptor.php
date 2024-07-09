<?php

namespace GoDaddy\WordPress\MWC\Core\Features\Shipping\Interceptors;

use Exception;
use GoDaddy\WordPress\MWC\Common\Helpers\ArrayHelper;
use GoDaddy\WordPress\MWC\Common\Interceptors\AbstractInterceptor;
use GoDaddy\WordPress\MWC\Common\Register\Register;

/**
 * Intercepts Shipping Settings page to display shipping labels service.
 */
class ShippingLabelsSettingsSectionInterceptor extends AbstractInterceptor
{
    /**
     * Adds filters to display shipping labels settings.
     *
     * @throws Exception
     */
    public function addHooks()
    {
        Register::filter()
            ->setGroup('woocommerce_get_sections_shipping')
            ->setHandler([$this, 'addShippingLabelsSection'])
            ->execute();

        Register::filter()
            ->setGroup('woocommerce_sections_shipping')
            ->setHandler([$this, 'outputShippingLabelsSettingsDiv'])
            ->setPriority(PHP_INT_MAX)
            ->execute();

        Register::action()
            ->setGroup('admin_print_styles')
            ->setHandler([$this, 'mayHideSaveSettingsButton'])
            ->execute();
    }

    /**
     * May add CSS to hide the save settings button.
     *
     * @internal
     * @return void
     */
    public function mayHideSaveSettingsButton() : void
    {
        // bail is current page is not shipping labels settings page
        if (! $this->isShippingLabelsSection()) {
            return;
        } ?>
        <style>
            button.button-primary.woocommerce-save-button {
                pointer-events: none;
                display: none;
            }
        </style>
        <?php
    }

    /**
     * Determines if the Shipping Labels section is the current open page.
     *
     * @return bool
     */
    protected function isShippingLabelsSection() : bool
    {
        return 'wc-settings' === ArrayHelper::get($_GET, 'page') &&
            'shipping' === ArrayHelper::get($_GET, 'tab') &&
            'labels' === ArrayHelper::get($_GET, 'section');
    }

    /**
     * Adds the Shipping labels tab to the WooCommerce Shipping settings screen.
     *
     * @internal
     * @param array $sections associative array of section keys and names
     * @return array
     */
    public function addShippingLabelsSection($sections)
    {
        if (is_array($sections)) {
            $sections['labels'] = __('Shipping labels', 'mwc-core');
        }

        return $sections;
    }

    /**
     * Outputs a div for Shipping Labels settings under the shipping labels tab.
     *
     * @internal
     */
    public function outputShippingLabelsSettingsDiv() : void
    {
        if ('labels' === ArrayHelper::get($_GET, 'section')) {
            echo '<div id="mwc-shipping-labels-settings"></div>';
        }
    }
}
