<?php

namespace GoDaddy\WordPress\MWC\Core\Features\Shipping\Interceptors;

use Exception;
use GoDaddy\WordPress\MWC\Common\Helpers\ArrayHelper;
use GoDaddy\WordPress\MWC\Common\Interceptors\AbstractInterceptor;
use GoDaddy\WordPress\MWC\Common\Register\Register;
use GoDaddy\WordPress\MWC\Common\Repositories\WooCommerceRepository;

/**
 * Intercepts Checkout page to require billing and shipping phone numbers.
 */
class RequireBillingPhoneAtCheckoutInterceptor extends AbstractInterceptor
{
    /**
     * Adds filters to display shipping labels settings.
     *
     * @throws Exception
     */
    public function addHooks() : void
    {
        Register::filter()
            ->setGroup('woocommerce_checkout_fields')
            ->setHandler([$this, 'requirePhoneFields'])
            ->execute();
    }

    /**
     * Require phone fields at checkout.
     *
     * @internal
     * @see \WC_Checkout::get_checkout_fields()
     * @see \woocommerce_form_field()
     *
     * @param mixed|array $checkoutFields
     * @return mixed|array
     */
    public function requirePhoneFields($checkoutFields)
    {
        if (! ArrayHelper::has($checkoutFields, 'billing.billing_phone')
            || ! WooCommerceRepository::isCheckoutPage()) {
            return $checkoutFields;
        }

        ArrayHelper::set($checkoutFields, 'billing.billing_phone.required', true);

        return $checkoutFields;
    }
}
