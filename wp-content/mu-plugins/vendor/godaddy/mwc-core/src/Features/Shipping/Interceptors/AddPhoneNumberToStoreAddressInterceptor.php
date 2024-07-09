<?php

namespace GoDaddy\WordPress\MWC\Core\Features\Shipping\Interceptors;

use Exception;
use GoDaddy\WordPress\MWC\Common\Exceptions\BaseException;
use GoDaddy\WordPress\MWC\Common\Helpers\ArrayHelper;
use GoDaddy\WordPress\MWC\Common\Interceptors\AbstractInterceptor;
use GoDaddy\WordPress\MWC\Common\Register\Register;

class AddPhoneNumberToStoreAddressInterceptor extends AbstractInterceptor
{
    public const PHONE_NUMBER_FIELD_NAME = 'mwc_store_phone';

    /**
     * {@inheritDoc}
     * @throws Exception
     */
    public function addHooks() : void
    {
        Register::filter()
            ->setGroup('woocommerce_general_settings')
            ->setHandler([$this, 'registerPhoneNumberSetting'])
            ->setPriority(PHP_INT_MAX)
            ->execute();
    }

    /**
     * Registers phone number field in the store address section.
     *
     * @internal
     *
     * @param array<int, mixed> $settings
     * @return array<int, mixed>
     * @throws BaseException
     */
    public function registerPhoneNumberSetting(array $settings) : array
    {
        foreach ($settings as $settingInfo) {
            if ('woocommerce_store_postcode' === ArrayHelper::get(ArrayHelper::wrap($settingInfo), 'id')) {
                $settings = ArrayHelper::insertAfter($settings, [
                    [
                        'title'    => __('Phone number', 'mwc-core'),
                        'desc'     => __('The phone number of your business.', 'mwc-core'),
                        'id'       => static::PHONE_NUMBER_FIELD_NAME,
                        'type'     => 'text',
                        'desc_tip' => true,
                        'required' => true,
                    ],
                ], $settingInfo);
                break;
            }
        }

        return $settings;
    }
}
