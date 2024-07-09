<?php

namespace GoDaddy\WordPress\MWC\Core\Features\Shipping\Interceptors;

use function current_user_can;
use Exception;
use GoDaddy\WordPress\MWC\Common\Components\Contracts\ComponentContract;
use GoDaddy\WordPress\MWC\Common\Components\Traits\HasComponentsTrait;
use GoDaddy\WordPress\MWC\Common\Interceptors\AbstractInterceptor;
use GoDaddy\WordPress\MWC\Common\Register\Exceptions\InvalidActionException;
use GoDaddy\WordPress\MWC\Common\Register\Register;
use GoDaddy\WordPress\MWC\Common\Repositories\WordPressRepository;

/**
 * This component is loaded even if the Shipping feature is not loaded for this request.
 */
class StoreLocationChangeInterceptor extends AbstractInterceptor
{
    use HasComponentsTrait;

    /** @var class-string<ComponentContract>[] alphabetically ordered list of components to load */
    protected $componentClasses = [
        RestoreLocationChangeNoticeInterceptor::class,
        EnqueueLocationChangeNoticeInterceptor::class,
        DisconnectShippingAccountOnLocationChangeInterceptor::class,
    ];

    /**
     * Determines whether the component should be loaded or not.
     *
     * @return bool
     */
    public static function shouldLoad() : bool
    {
        // try to limit processing to document requests initiated by a merchant on the admin dashboard
        return is_admin() && ! WordPressRepository::isAjax() && ! WordPressRepository::isApiRequest() && current_user_can('manage_woocommerce');
    }

    /**
     * Registers actions used to monitor changes in the store location.
     *
     * @throws InvalidActionException
     */
    public function addHooks() : void
    {
        $action = Register::action()
            ->setGroup('wp_loaded')
            ->setHandler(function () {
                $this->loadComponents();
            })
            ->setPriority(1000);

        /** @throws InvalidActionException {@see RegisterAction::execute()} really throws {@see InvalidActionException} instead of {@see Exception} */
        $action->execute();
    }
}
