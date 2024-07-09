<?php

namespace GoDaddy\WordPress\MWC\Core\Features\Worldpay;

use Exception;
use GoDaddy\WordPress\MWC\Common\Components\Contracts\ComponentContract;
use GoDaddy\WordPress\MWC\Common\Components\Traits\HasComponentsTrait;
use GoDaddy\WordPress\MWC\Common\Configuration\Configuration;
use GoDaddy\WordPress\MWC\Common\Features\AbstractFeature;
use GoDaddy\WordPress\MWC\Common\Platforms\PlatformRepositoryFactory;
use GoDaddy\WordPress\MWC\Core\Features\Worldpay\Interceptors\ConnectionInterceptor;
use GoDaddy\WordPress\MWC\Core\Features\Worldpay\Interceptors\OverridesInterceptor;

/**
 * The Worldpay feature loader.
 */
class Worldpay extends AbstractFeature
{
    use HasComponentsTrait;

    /** @var class-string<ComponentContract>[] component classes to load */
    protected $componentClasses = [
        ConnectionInterceptor::class,
        OverridesInterceptor::class,
    ];

    /**
     * Gets the feature name, matching the key used in configuration.
     *
     * @return string
     */
    public static function getName() : string
    {
        return 'worldpay';
    }

    /**
     * Determines whether the class should load.
     *
     * @return bool
     * @throws Exception
     */
    public static function shouldLoad() : bool
    {
        return parent::shouldLoadFeature()
            && PlatformRepositoryFactory::getNewInstance()->getPlatformRepository()->getGoDaddyCustomer()->getFederationPartnerId() === 'WORLDPAY'
            && 'woosaas' === PlatformRepositoryFactory::getNewInstance()->getPlatformRepository()->getPlatformName();
    }

    /**
     * Initializes this feature.
     *
     * @throws Exception
     */
    public function load() : void
    {
        $this->loadComponents();
    }

    /**
     * Determines whether Worldpay should be loaded with temporary content to the pages.
     *
     * By temporary content, consider new URLs, hooks that may behave with or without this conditional, among others.
     *
     * @return bool
     * @throws Exception
     */
    public static function shouldProcessTemporaryContent() : bool
    {
        return Configuration::get('features.worldpay.useNewUrls', false) && static::shouldLoad();
    }
}
