<?php

namespace GoDaddy\WordPress\MWC\Core\Auth\Sso\WordPress;

use GoDaddy\WordPress\MWC\Common\Components\Contracts\ComponentContract;
use GoDaddy\WordPress\MWC\Common\Components\Exceptions\ComponentClassesNotDefinedException;
use GoDaddy\WordPress\MWC\Common\Components\Exceptions\ComponentLoadFailedException;
use GoDaddy\WordPress\MWC\Common\Components\Traits\HasComponentsFromContainerTrait;
use GoDaddy\WordPress\MWC\Common\Features\AbstractFeature;
use GoDaddy\WordPress\MWC\Core\Auth\Sso\WordPress\Interceptors\CareAgentLoginInterceptor;
use GoDaddy\WordPress\MWC\Core\Auth\Sso\WordPress\Interceptors\MainAdminDeleteInterceptor;
use GoDaddy\WordPress\MWC\Core\Auth\Sso\WordPress\Interceptors\SsoInterceptor;
use GoDaddy\WordPress\MWC\Core\Auth\Sso\WordPress\Interceptors\UserDeleteInterceptor;

class WordPressSso extends AbstractFeature
{
    use HasComponentsFromContainerTrait;

    /** @var class-string<ComponentContract>[] alphabetically ordered list of components to load */
    protected array $componentClasses = [
        CareAgentLoginInterceptor::class,
        MainAdminDeleteInterceptor::class,
        SsoInterceptor::class,
        UserDeleteInterceptor::class,
    ];

    /**
     * {@inheritDoc}
     */
    public static function getName() : string
    {
        return 'wordpress_sso';
    }

    /**
     * Initializes the components.
     *
     * @return void
     * @throws ComponentClassesNotDefinedException
     * @throws ComponentLoadFailedException
     */
    public function load() : void
    {
        $this->loadComponents();
    }
}
