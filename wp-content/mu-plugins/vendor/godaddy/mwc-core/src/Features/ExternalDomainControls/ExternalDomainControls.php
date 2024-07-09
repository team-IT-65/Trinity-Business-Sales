<?php

namespace GoDaddy\WordPress\MWC\Core\Features\ExternalDomainControls;

use GoDaddy\WordPress\MWC\Common\Components\Contracts\ComponentContract;
use GoDaddy\WordPress\MWC\Common\Components\Exceptions\ComponentClassesNotDefinedException;
use GoDaddy\WordPress\MWC\Common\Components\Exceptions\ComponentLoadFailedException;
use GoDaddy\WordPress\MWC\Common\Components\Traits\HasComponentsFromContainerTrait;
use GoDaddy\WordPress\MWC\Common\Features\AbstractFeature;
use GoDaddy\WordPress\MWC\Core\Features\ExternalDomainControls\Interceptors\DomainAttachNoticeInterceptor;
use GoDaddy\WordPress\MWC\Core\WordPress\Interceptors\GeneralSettingsInterceptor;

/**
 * Controls domain attach behaviour.
 */
class ExternalDomainControls extends AbstractFeature
{
    use HasComponentsFromContainerTrait;

    /** @var array<class-string<ComponentContract>> */
    protected array $componentClasses = [
        DomainAttachNoticeInterceptor::class,
        GeneralSettingsInterceptor::class,
    ];

    /**
     * {@inheritDoc}
     */
    public static function getName() : string
    {
        return 'external_domain_controls';
    }

    /**
     * @return void
     * @throws ComponentClassesNotDefinedException
     * @throws ComponentLoadFailedException
     */
    public function load() : void
    {
        $this->loadComponents();
    }
}
