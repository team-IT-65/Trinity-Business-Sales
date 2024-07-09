<?php

namespace GoDaddy\WordPress\MWC\Core\Features\Shipping;

use Exception;
use GoDaddy\WordPress\MWC\Common\Components\Contracts\ComponentContract;
use GoDaddy\WordPress\MWC\Common\Components\Traits\HasComponentsTrait;
use GoDaddy\WordPress\MWC\Common\Features\AbstractFeature;
use GoDaddy\WordPress\MWC\Common\Helpers\ArrayHelper;
use GoDaddy\WordPress\MWC\Common\Repositories\WooCommerceRepository;
use GoDaddy\WordPress\MWC\Core\Features\Shipping\API\API;
use GoDaddy\WordPress\MWC\Core\Features\Shipping\Interceptors\AddPhoneNumberToStoreAddressInterceptor;
use GoDaddy\WordPress\MWC\Core\Features\Shipping\Interceptors\CopyBillingPhoneAsShippingPhoneInterceptor;
use GoDaddy\WordPress\MWC\Core\Features\Shipping\Interceptors\RedirectToShippingDashboardInterceptor;
use GoDaddy\WordPress\MWC\Core\Features\Shipping\Interceptors\RequireBillingPhoneAtCheckoutInterceptor;
use GoDaddy\WordPress\MWC\Core\Features\Shipping\Interceptors\ShippingLabelsSettingsSectionInterceptor;
use GoDaddy\WordPress\MWC\Core\Features\Shipping\Services\ShippingAccountService;
use GoDaddy\WordPress\MWC\Shipping\Models\Account\Statuses\ConnectedStatus;

class Shipping extends AbstractFeature
{
    use HasComponentsTrait;

    /** @var class-string<ComponentContract>[] alphabetically ordered list of components to load */
    protected $componentClasses = [
        Lifecycle::class,
        API::class,
        ShippingLabelsSettingsSectionInterceptor::class,
        RedirectToShippingDashboardInterceptor::class,
        AddPhoneNumberToStoreAddressInterceptor::class,
        RequireBillingPhoneAtCheckoutInterceptor::class,
        CopyBillingPhoneAsShippingPhoneInterceptor::class,
    ];

    /**
     * Gets the feature name, matching the key used in configuration.
     *
     * @return string
     */
    public static function getName() : string
    {
        return 'shipping';
    }

    /**
     * Determines whether the class should load.
     *
     * @return bool
     */
    public static function shouldLoad() : bool
    {
        return static::shouldLoadFeature() && static::isBaseCountryEligible();
    }

    /**
     * Determines whether the store's base country is eligible to use the feature.
     *
     * @return bool
     */
    public static function isBaseCountryEligible() : bool
    {
        return ArrayHelper::contains(['us', 'gb', 'ca', 'au'], strtolower(WooCommerceRepository::getBaseCountry()));
    }

    /**
     * Determines whether the Shipping feature ever loaded on this site.
     *
     * @return bool
     */
    public static function loadedBefore() : bool
    {
        return get_option(Lifecycle::SHIPPING_PREVIOUSLY_LOADED_OPTION_NAME) === 'yes';
    }

    /**
     * Determines whether the Shipping account for this site is already connected.
     *
     * @return bool
     */
    public static function isAccountConnected() : bool
    {
        return ShippingAccountService::getNewInstance()->getAccount()->getStatus() instanceof ConnectedStatus;
    }

    /**
     * Initializes the feature.
     *
     * @throws Exception
     */
    public function load()
    {
        $this->loadComponents();
    }
}
