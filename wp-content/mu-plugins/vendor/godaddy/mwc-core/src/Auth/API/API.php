<?php

namespace GoDaddy\WordPress\MWC\Core\Auth\API;

use Exception;
use GoDaddy\WordPress\MWC\Common\Components\Contracts\ComponentContract;
use GoDaddy\WordPress\MWC\Common\Components\Exceptions\ComponentClassesNotDefinedException;
use GoDaddy\WordPress\MWC\Common\Components\Exceptions\ComponentLoadFailedException;
use GoDaddy\WordPress\MWC\Common\Components\Traits\HasComponentsTrait;
use GoDaddy\WordPress\MWC\Common\Register\Register;
use GoDaddy\WordPress\MWC\Core\Auth\API\Controllers\TokenController;

class API implements ComponentContract
{
    use HasComponentsTrait;
    /** @var array controller classes to load/register */
    protected $componentClasses = [
        TokenController::class,
    ];

    /**
     * Loads the API component.
     *
     * @throws Exception
     */
    public function load()
    {
        Register::action()
            ->setGroup('rest_api_init')
            ->setHandler([$this, 'registerRoutes'])
            ->execute();
    }

    /**
     * Registers the onboarding REST API routes.
     *
     * @throws ComponentLoadFailedException
     * @throws ComponentClassesNotDefinedException
     *
     * @see \GoDaddy\WordPress\MWC\Core\Features\Shipping\\GoDaddy\WordPress\MWC\Core\Features\Shipping\API\API::load()
     * @internal
     */
    public function registerRoutes()
    {
        $this->loadComponents();
    }
}
