<?php

namespace GoDaddy\WordPress\MWC\Core\Features\Shipping\Interceptors;

use Exception;
use GoDaddy\WordPress\MWC\Common\Helpers\ArrayHelper;
use GoDaddy\WordPress\MWC\Common\Interceptors\AbstractInterceptor;
use GoDaddy\WordPress\MWC\Common\Register\Exceptions\InvalidActionException;
use GoDaddy\WordPress\MWC\Common\Register\Register;
use GoDaddy\WordPress\MWC\Common\Repositories\WooCommerceRepository;
use GoDaddy\WordPress\MWC\Core\Admin\Notices\Notices;
use GoDaddy\WordPress\MWC\Core\Features\Shipping\Notices\LocationChangeNotice;
use GoDaddy\WordPress\MWC\Core\Features\Shipping\Shipping;

class EnqueueLocationChangeNoticeInterceptor extends AbstractInterceptor
{
    /**
     * Determines whether the component should be loaded or not.
     *
     * @return bool
     */
    public static function shouldLoad() : bool
    {
        return ! Shipping::shouldLoad()
            && Shipping::loadedBefore()
            && ! Shipping::isBaseCountryEligible()
            && static::isPageEligible();
    }

    /**
     * Determines whether the notice should be enqueued in the current page.
     *
     * @return bool
     */
    protected static function isPageEligible() : bool
    {
        return static::isOrdersPage()
            || static::isEditOrderPage()
            || static::isShippingSettingsPage();
    }

    /**
     * Determines whether the current page is the Orders admin page.
     *
     * @return bool
     */
    protected static function isOrdersPage() : bool
    {
        return WooCommerceRepository::isCustomOrdersTableUsageEnabled()
            ? ArrayHelper::get($_GET, 'page') === 'wc-orders'
            : static::isPostListPage('shop_order');
    }

    /**
     * Determines whether the current is the list page for a post type.
     *
     * @param string $postType
     * @return bool
     */
    protected static function isPostListPage(string $postType = 'post') : bool
    {
        return ArrayHelper::get($GLOBALS, 'pagenow') === 'edit.php'
            && ArrayHelper::get($_GET, 'post_type') === $postType;
    }

    /**
     * Determines whether the current page is the Edit Order page.
     *
     * @return bool
     */
    protected static function isEditOrderPage() : bool
    {
        return WooCommerceRepository::isCustomOrdersTableUsageEnabled()
            ? static::isOrdersPage() && ArrayHelper::get($_GET, 'action') === 'edit'
            : static::isEditPostPage('shop_order');
    }

    /**
     * Determines whether the current page is the edit page for a post type.
     *
     * @param string $postType
     * @return bool
     */
    protected static function isEditPostPage(string $postType = 'post') : bool
    {
        return ArrayHelper::get($GLOBALS, 'pagenow') === 'post.php'
            && ArrayHelper::get($_GET, 'action') === 'edit'
            && get_post_type(ArrayHelper::get($_GET, 'post')) === $postType;
    }

    /**
     * Determines whether the current page is the WooCommerce > Settings > Shipping admin page.
     *
     * @return bool
     */
    protected static function isShippingSettingsPage() : bool
    {
        return ArrayHelper::get($_GET, 'page') === 'wc-settings'
            && ArrayHelper::get($_GET, 'tab') === 'shipping';
    }

    /**
     * Adds filters and actions to display a notice if the location changes from a supported region to an unsupported one.
     *
     * @throws InvalidActionException
     */
    public function addHooks() : void
    {
        $action = Register::action()
            ->setGroup('admin_init')
            ->setHandler([$this, 'enqueueLocationChangeNotice']);

        /** @throws InvalidActionException {@see RegisterAction::execute()} really throws {@see InvalidActionException} instead of {@see Exception} */
        $action->execute();
    }

    /**
     * Enqueues an admin notice shown when the store location changes to an unsupported region.
     */
    public function enqueueLocationChangeNotice() : void
    {
        Notices::enqueueAdminNotice(LocationChangeNotice::getNewInstance());
    }
}
