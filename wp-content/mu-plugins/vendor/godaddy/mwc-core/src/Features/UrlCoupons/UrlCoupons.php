<?php

namespace GoDaddy\WordPress\MWC\Core\Features\UrlCoupons;

use Exception;
use GoDaddy\WordPress\MWC\Common\Configuration\Configuration;
use GoDaddy\WordPress\MWC\Common\Events\Events;
use GoDaddy\WordPress\MWC\Common\Features\AbstractFeature;
use GoDaddy\WordPress\MWC\Common\Helpers\StringHelper;
use GoDaddy\WordPress\MWC\Common\Platforms\Exceptions\PlatformRepositoryException;
use GoDaddy\WordPress\MWC\Common\Platforms\PlatformRepositoryFactory;
use GoDaddy\WordPress\MWC\Common\Register\Register;
use GoDaddy\WordPress\MWC\Core\Events\FeatureEnabledEvent;
use GoDaddy\WordPress\MWC\Core\WooCommerce\Views\Components\GoDaddyBranding;
use function GoDaddy\WordPress\MWC\UrlCoupons\wc_url_coupons;
use GoDaddy\WordPress\MWC\UrlCoupons\WC_URL_Coupons_Loader;

/**
 * The URL Coupons feature loader.
 */
class UrlCoupons extends AbstractFeature
{
    /** @var string the plugin name */
    protected static $communityPluginName = 'woocommerce-url-coupons/woocommerce-url-coupons.php';

    /** @var string the community plugin slug */
    protected static $communityPluginSlug = 'woocommerce-url-coupons';

    /**
     * Gets the feature name, matching the key used in configuration.
     *
     * @return string
     */
    public static function getName() : string
    {
        return 'url_coupons';
    }

    /**
     * {@inheritdoc}
     * @throws Exception
     */
    public function load()
    {
        $rootVendorPath = StringHelper::trailingSlash(Configuration::get('system_plugin.project_root').'/vendor');

        // load plugin class file
        require_once $rootVendorPath.'godaddy/mwc-url-coupons/woocommerce-url-coupons.php';

        // load SV Framework from root vendor folder first
        require_once $rootVendorPath.'skyverge/wc-plugin-framework/woocommerce/class-sv-wc-plugin.php';

        WC_URL_Coupons_Loader::instance()->init_plugin();

        $this->registerHooks();
    }

    /**
     * Registers hooks.
     *
     * @throws Exception
     */
    protected function registerHooks()
    {
        Register::action()
            ->setGroup('admin_init')
            ->setHandler([$this, 'maybeDeactivateUrlCouponsPlugins'])
            ->execute();

        Register::action()
                ->setGroup('mwc_coupon_options_discount_links')
                ->setHandler([$this, 'addGoDaddyBrandingStyles'])
                ->setCondition([$this, 'shouldAddGoDaddyBranding'])
                ->execute();

        Register::action()
                ->setGroup('mwc_coupon_options_discount_links')
                ->setHandler([GoDaddyBranding::getInstance(), 'render'])
                ->setCondition([$this, 'shouldAddGoDaddyBranding'])
                ->execute();

        Register::action()
                ->setGroup('load-plugins.php')
                ->setHandler([$this, 'removePluginUpdateNotice'])
                ->setPriority(PHP_INT_MAX)
                ->execute();
    }

    /**
     * Removes the WP action that displays the plugin update notice below each plugin on the Plugins page.
     */
    public function removePluginUpdateNotice()
    {
        remove_action('after_plugin_row_'.static::$communityPluginName, 'wp_plugin_update_row');
    }

    /**
     * Checks if should add GoDaddy branding to module settings page.
     *
     * @return bool
     * @throws PlatformRepositoryException
     */
    public function shouldAddGoDaddyBranding() : bool
    {
        return ! PlatformRepositoryFactory::getNewInstance()->getPlatformRepository()->isReseller()
            // only add branding if another feature is not already adding it
            && ! has_action('mwc_coupon_options_discount_links', [GoDaddyBranding::getInstance(), 'render']);
    }

    /**
     * Adds the style tag used by the GoDaddy branding.
     */
    public function addGoDaddyBrandingStyles()
    {
        ob_start(); ?>
        <style>
            .mwc-gd-branding {
                margin: 9px;
                line-height: 0;
                padding-top: 24px;
                width: 120px;
            }
        </style>
        <?php

        (GoDaddyBranding::getInstance())->addStyle(ob_get_clean());
    }

    /**
     * May deactivate the URL Coupons plugin.
     *
     * @throws Exception
     */
    public function maybeDeactivateUrlCouponsPlugins()
    {
        if (! static::isUrlCouponsPluginActive()) {
            return;
        }

        update_option('mwc_url_coupons_show_notice_plugin_users', 'yes');

        // we want to display the notice again even it was previously dismissed
        wc_url_coupons()->get_admin_notice_handler()->undismiss_notice(wc_url_coupons()->get_id_dasherized().'-plugin-users');

        if (function_exists('deactivate_plugins')) {
            deactivate_plugins(static::$communityPluginName);

            // unset GET param so that the "Plugin activated." notice is not displayed
            if (isset($_GET['activate'])) {
                unset($_GET['activate']);
            }

            Events::broadcast(new FeatureEnabledEvent('url_coupons'));
        }
    }

    /**
     * Checks if URL Coupons plugin is active.
     *
     * @return bool
     */
    public static function isUrlCouponsPluginActive() : bool
    {
        return function_exists('is_plugin_active') && is_plugin_active(static::$communityPluginName);
    }
}
