<?php

namespace GoDaddy\WordPress\MWC\Core\Features\Shipping\Interceptors;

use GoDaddy\WordPress\MWC\Common\Helpers\ArrayHelper;
use GoDaddy\WordPress\MWC\Common\Helpers\StringHelper;
use GoDaddy\WordPress\MWC\Common\Interceptors\AbstractInterceptor;
use GoDaddy\WordPress\MWC\Common\Register\Exceptions\InvalidFilterException;
use GoDaddy\WordPress\MWC\Common\Register\Register;
use GoDaddy\WordPress\MWC\Common\Repositories\WooCommerceRepository;

class CopyBillingPhoneAsShippingPhoneInterceptor extends AbstractInterceptor
{
    /**
     * Adds filters to copy the billing phone number as the shipping phone number.
     *
     * @throws InvalidFilterException
     */
    public function addHooks() : void
    {
        $filter = Register::filter()
            ->setGroup('woocommerce_checkout_posted_data')
            ->setHandler([$this, 'maybeCopyBillingPhone']);

        /** @throws InvalidFilterException */
        $filter->execute();
    }

    /**
     * Copies the submitted billing phone and uses it as the shipping phone.
     *
     * @internal
     *
     * @param array<string, mixed>|mixed $data
     * @return array<string, mixed>|mixed
     */
    public function maybeCopyBillingPhone($data)
    {
        if ($phone = $this->getBillingPhoneToCopy(ArrayHelper::wrap($data))) {
            ArrayHelper::set($data, 'shipping_phone', $phone);
        }

        return $data;
    }

    /**
     * Gets a billing phone that can be used as the shipping phone.
     *
     * @param array<string, mixed> $data
     * @return string|null
     */
    protected function getBillingPhoneToCopy(array $data) : ?string
    {
        if (! $this->shouldCopyBillingPhone($data)) {
            return null;
        }

        return (string) StringHelper::ensureScalar(ArrayHelper::get($data, 'billing_phone'));
    }

    /**
     * Checks the submitted data and cart information to determine whether we can use the billing phone as the shipping phone.
     *
     * @param array<string, mixed> $data
     * @return bool
     */
    protected function shouldCopyBillingPhone(array $data) : bool
    {
        if (ArrayHelper::get($data, 'ship_to_different_address')) {
            return false;
        }

        if (ArrayHelper::get($data, 'shipping_phone')) {
            return false;
        }

        if (! $wc = WooCommerceRepository::getInstance()) {
            return false;
        }

        return $wc->cart->needs_shipping_address();
    }
}
