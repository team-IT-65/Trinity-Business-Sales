<?php

namespace GoDaddy\WordPress\MWC\Core\Analytics\Services;

use Exception;
use GoDaddy\WordPress\MWC\Common\Helpers\ArrayHelper;
use GoDaddy\WordPress\MWC\Common\Helpers\StringHelper;
use GoDaddy\WordPress\MWC\Common\Helpers\TypeHelper;
use GoDaddy\WordPress\MWC\Common\Repositories\WooCommerce\ProductsRepository;
use GoDaddy\WordPress\MWC\Common\Repositories\WooCommerce\SessionRepository;
use GoDaddy\WordPress\MWC\Common\Repositories\WooCommerceRepository;
use GoDaddy\WordPress\MWC\Common\Repositories\WordPressRepository;
use GoDaddy\WordPress\MWC\Common\Traits\CanGetNewInstanceTrait;
use GoDaddy\WordPress\MWC\Core\Analytics\Adapters\CheckoutEventAdapter;
use GoDaddy\WordPress\MWC\Core\Analytics\Adapters\OrderEventAdapter;
use GoDaddy\WordPress\MWC\Core\Analytics\Adapters\ProductEventDataAdapter;
use GoDaddy\WordPress\MWC\Core\Analytics\DataObjects\EventData;
use GoDaddy\WordPress\MWC\Core\Analytics\Interceptors\ScriptEventDataInterceptor;
use GoDaddy\WordPress\MWC\Core\WooCommerce\Repositories\OrdersRepository;

/**
 * Contextual event data service class.
 *
 * This class helps to build event data for objects in context.
 */
class ContextualEventDataService
{
    use CanGetNewInstanceTrait;

    /** @var string */
    const ORDER_PURCHASE_EVENT_TRACKED_META_KEY = '_analytics_purchase_event_tracked';

    /** @var bool */
    protected bool $fireViewProduct = false;

    /** @var bool */
    protected bool $fireProductAddedToCart = false;

    /** @var bool */
    protected bool $fireProductRemovedFromCart = false;

    /** @var bool */
    protected bool $fireBeginCheckout = false;

    /** @var bool */
    protected bool $firePurchased = false;

    /** @var EventData|null */
    protected ?EventData $eventData = null;

    /**
     * Builds the event data for the object in context.
     *
     * @return array<string, mixed>
     */
    public function build() : array
    {
        if ($this->isProductRemovedFromCart()) {
            $this->buildProductRemovedFromCartData();
        } elseif ($this->isProductAddedToCart()) {
            $this->buildProductAddedToCartData();
        } elseif (WooCommerceRepository::isProductPage()) {
            $this->buildSingleProductData();
        } elseif (WooCommerceRepository::isOrderReceivedPage()) {
            $this->buildOrderData();
        } elseif (WooCommerceRepository::isCheckoutPage()) {
            $this->buildCheckoutData();
        }

        return [
            'flags' => [
                'ajaxAddToCartEnabled' => 'yes' === get_option('woocommerce_enable_ajax_add_to_cart'),
            ],
            'events' => [
                'fireViewEvent'            => $this->fireViewProduct,
                'fireAddedToCartEvent'     => $this->fireProductAddedToCart,
                'fireRemovedFromCartEvent' => $this->fireProductRemovedFromCart,
                'fireCheckoutEvent'        => $this->fireBeginCheckout,
                'firePurchasedEvent'       => $this->firePurchased,
            ],
            'data' => $this->eventData ? $this->eventData->toArray() : [],
        ];
    }

    /**
     * Builds the single product event data.
     *
     * @return void
     */
    public function buildSingleProductData() : void
    {
        $sourceProduct = ProductsRepository::getCurrent();

        if (! $sourceProduct) {
            return;
        }

        $this->fireViewProduct = true;

        $this->eventData = ProductEventDataAdapter::getNewInstance($sourceProduct)->convertFromSource();
    }

    /**
     * Determines if a product was added to cart.
     *
     * @see ScriptEventDataInterceptor::addProductAddedToCartToRedirectUrl()
     *
     * @return bool
     */
    protected function isProductAddedToCart() : bool
    {
        $productId = TypeHelper::int(ArrayHelper::get($_REQUEST, ScriptEventDataInterceptor::PRODUCT_ADDED_TO_CART_KEY), 0);

        return $productId > 0;
    }

    /**
     * Builds the product added to cart event data.
     *
     * @return void
     */
    public function buildProductAddedToCartData() : void
    {
        $sourceProduct = ProductsRepository::get(TypeHelper::int(ArrayHelper::get($_REQUEST, ScriptEventDataInterceptor::PRODUCT_ADDED_TO_CART_KEY), 0));

        if (! $sourceProduct) {
            return;
        }

        $quantity = TypeHelper::float(ArrayHelper::get($_REQUEST, ScriptEventDataInterceptor::PRODUCT_QUANTITY_KEY), 1.0);

        $this->fireProductAddedToCart = true;

        $this->eventData = ProductEventDataAdapter::getNewInstance($sourceProduct, $quantity)->convertFromSource();
    }

    /**
     * Determines if a product was removed from cart.
     *
     * @return bool
     */
    protected function isProductRemovedFromCart() : bool
    {
        $productId = TypeHelper::int(ArrayHelper::get($_REQUEST, ScriptEventDataInterceptor::PRODUCT_REMOVED_FROM_CART_KEY), 0);

        return $productId > 0;
    }

    /**
     * Builds the product removed from cart event data.
     *
     * @return void
     */
    public function buildProductRemovedFromCartData() : void
    {
        $sourceProduct = ProductsRepository::get(TypeHelper::int(ArrayHelper::get($_REQUEST, ScriptEventDataInterceptor::PRODUCT_REMOVED_FROM_CART_KEY), 0));

        if (! $sourceProduct) {
            return;
        }

        $quantity = TypeHelper::float(ArrayHelper::get($_REQUEST, ScriptEventDataInterceptor::PRODUCT_QUANTITY_KEY), 1.0);

        $this->fireProductRemovedFromCart = true;

        $this->eventData = ProductEventDataAdapter::getNewInstance($sourceProduct, $quantity)->convertFromSource();
    }

    /**
     * Builds the order event data.
     *
     * @return void
     */
    public function buildOrderData() : void
    {
        try {
            $wp = WordPressRepository::getInstance();

            $orderId = TypeHelper::int($wp->query_vars['order-received'] ?: 0, 0);
            $order = $orderId > 0 ? OrdersRepository::get($orderId) : null;

            if (! $order || ! empty($order->get_meta(static::ORDER_PURCHASE_EVENT_TRACKED_META_KEY))) {
                return;
            }

            $this->eventData = OrderEventAdapter::getNewInstance($order)->convertFromSource();

            $order->update_meta_data(static::ORDER_PURCHASE_EVENT_TRACKED_META_KEY, (string) time());
            $order->save_meta_data();
        } catch (Exception $exception) {
            // as we might be in a hook callback context, we catch any exceptions instead of throwing
            return;
        }

        $this->firePurchased = true;
    }

    /**
     * Builds the checkout event data.
     *
     * @return void
     */
    public function buildCheckoutData() : void
    {
        try {
            $session = SessionRepository::getInstance();

            if (! is_callable([$session, 'get_session_data'])) {
                return;
            }

            $sessionData = TypeHelper::array(StringHelper::maybeUnserializeRecursively($session->get_session_data()), []);

            if (empty($sessionData)) {
                return;
            }

            $this->eventData = CheckoutEventAdapter::getNewInstance($sessionData)->convertFromSource();
            $this->fireBeginCheckout = true;
        } catch (Exception $exception) {
            // as we might be in a hook callback context, we catch any exceptions instead of throwing
            return;
        }
    }
}
