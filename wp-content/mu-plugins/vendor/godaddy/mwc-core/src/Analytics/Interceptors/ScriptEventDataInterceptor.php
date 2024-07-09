<?php

namespace GoDaddy\WordPress\MWC\Core\Analytics\Interceptors;

use Exception;
use GoDaddy\WordPress\MWC\Common\Configuration\Configuration;
use GoDaddy\WordPress\MWC\Common\Enqueue\Enqueue;
use GoDaddy\WordPress\MWC\Common\Helpers\ArrayHelper;
use GoDaddy\WordPress\MWC\Common\Helpers\TypeHelper;
use GoDaddy\WordPress\MWC\Common\Http\Url;
use GoDaddy\WordPress\MWC\Common\Register\Register;
use GoDaddy\WordPress\MWC\Common\Repositories\WooCommerce\CartRepository;
use GoDaddy\WordPress\MWC\Common\Repositories\WooCommerce\ProductsRepository;
use GoDaddy\WordPress\MWC\Common\Repositories\WooCommerceRepository;
use GoDaddy\WordPress\MWC\Common\Repositories\WordPressRepository;
use GoDaddy\WordPress\MWC\Core\Analytics\Services\ContextualEventDataService;
use GoDaddy\WordPress\MWC\Core\WooCommerce\Adapters\ProductAdapter;
use WC_Form_Handler;
use WC_Product;

/**
 * Interceptor for common analytics JavaScript and other hooks.
 */
class ScriptEventDataInterceptor extends AbstractAnalyticsEventInterceptor
{
    /** @var string name of the JS file handle */
    const SCRIPT_HANDLE = 'gd-analytics';

    /** @var string */
    const PRODUCT_ADDED_TO_CART_KEY = 'product_added_to_cart';

    /** @var string */
    const PRODUCT_REMOVED_FROM_CART_KEY = 'product_removed_from_cart';

    /** @var string */
    const PRODUCT_QUANTITY_KEY = 'quantity';

    /**
     * Adds hooks.
     *
     * @return void
     * @throws Exception
     */
    public function addHooks() : void
    {
        parent::addHooks();

        Register::filter()
            ->setGroup('woocommerce_loop_add_to_cart_args')
            ->setHandler([$this, 'addProductDataToAddToCartLink'])
            ->setArgumentsCount(2)
            ->setCondition(function () {
                return 'yes' === get_option('woocommerce_enable_ajax_add_to_cart');
            })
            ->execute();

        Register::filter()
            ->setGroup('woocommerce_add_to_cart_redirect')
            ->setHandler([$this, 'addProductAddedToCartToRedirectUrl'])
            ->setPriority(PHP_INT_MAX)
            ->setArgumentsCount(2)
            ->execute();

        Register::filter()
            ->setGroup('woocommerce_cart_item_remove_link')
            ->setHandler([$this, 'updateRemoveProductFromCartLink'])
            ->setPriority(PHP_INT_MIN)
            ->setArgumentsCount(2)
            ->execute();

        Register::filter()
            ->setGroup('wp_redirect')
            ->setHandler([$this, 'addProductRemovedFromCartToRedirectUrl'])
            ->setPriority(PHP_INT_MAX)
            ->setCondition(function () {
                return 'yes' !== get_option('woocommerce_enable_ajax_add_to_cart');
            })
            ->execute();
    }

    /**
     * Enqueues the generic analytics JavaScript file.
     *
     * @return void
     * @throws Exception
     */
    protected function enqueueJs() : void
    {
        Enqueue::script()
            ->setHandle(static::SCRIPT_HANDLE)
            ->setSource(WordPressRepository::getAssetsUrl('js/analytics/frontend/analytics-events.js'))
            ->setVersion(TypeHelper::string(Configuration::get('mwc.version'), '1.0'))
            ->setDeferred(true)
            ->attachInlineScriptObject('gdAnalytics')
            ->attachInlineScriptVariables($this->getInlineVariables())
            ->execute();
    }

    /**
     * Gets the inline JavaScript variables.
     *
     * @return array<string, mixed>
     */
    protected function getInlineVariables() : array
    {
        return ContextualEventDataService::getNewInstance()->build();
    }

    /**
     * Adds extra data to add to cart button attributes, so that we can use them in JavaScript.
     *
     * @internal
     * @see woocommerce_template_loop_add_to_cart()
     *
     * @param array<mixed>|mixed $args
     * @param WC_Product|mixed $product
     * @return array<mixed>|mixed
     */
    public function addProductDataToAddToCartLink($args, $product)
    {
        if (! ArrayHelper::accessible($args) || ! $product instanceof WC_Product) {
            return $args;
        }

        $args['attributes']['data-product_price'] = TypeHelper::string($product->get_price(), '0.00');
        $args['attributes']['data-product_name'] = $product->get_name();
        $args['attributes']['data-google_product_id'] = $product->get_meta(ProductAdapter::MARKETPLACES_GOOGLE_PRODUCT_ID);

        return $args;
    }

    /**
     * Adds a query argument to the URL WooCommerce redirects to after adding a product to the cart.
     *
     * @internal
     * @see WC_Form_Handler::add_to_cart_action()
     *
     * @param string|mixed $url
     * @param WC_Product|mixed $productAddedToCart
     * @return string|mixed
     */
    public function addProductAddedToCartToRedirectUrl($url, $productAddedToCart = null)
    {
        // bail early if this is an AJAX request to avoid breaking add-to-cart functionality when using AJAX
        if (WordPressRepository::isAjax()) {
            return $url;
        }

        if (empty($url) && 'yes' === get_option('woocommerce_cart_redirect_after_add')) {
            $url = WooCommerceRepository::getCartPageUrl();
        }

        if (! is_string($url)) {
            return $url;
        }

        // this may be necessary as sometimes the filter does not pass a valid product, but the product ID is present in the request
        if (! $productAddedToCart instanceof WC_Product) {
            $productId = TypeHelper::int(ArrayHelper::get($_REQUEST, 'add-to-cart'), 0);
            $productAddedToCart = $productId > 0 ? ProductsRepository::get($productId) : null;

            if (! $productAddedToCart) {
                return $url;
            }
        }

        try {
            return Url::fromString($url)
                ->removeQueryParameter(static::PRODUCT_REMOVED_FROM_CART_KEY)
                ->addQueryParameter(static::PRODUCT_ADDED_TO_CART_KEY, $productAddedToCart->get_id())
                ->addQueryParameter(static::PRODUCT_QUANTITY_KEY, TypeHelper::float(ArrayHelper::get($_REQUEST, 'quantity'), 1))
                ->toString();
        } catch (Exception $exception) {
            // since we are in a hook callback context we catch the exception instead of throwing
            return $url;
        }
    }

    /**
     * Replaces the remove from cart link element with a link element containing additional product data.
     *
     * @internal
     *
     * @param string|mixed $removeFromCartLink
     * @param string|mixed $cartItemKey
     * @return string|mixed
     */
    public function updateRemoveProductFromCartLink($removeFromCartLink, $cartItemKey)
    {
        if (! is_string($cartItemKey)) {
            return $removeFromCartLink;
        }

        try {
            $cart = CartRepository::getInstance();
        } catch (Exception $exception) {
            return $removeFromCartLink;
        }

        $cartItem = TypeHelper::array(ArrayHelper::get($cart->get_cart_contents(), $cartItemKey), []);
        $variationId = TypeHelper::int(ArrayHelper::get($cartItem, 'variation_id'), 0);
        $productId = $variationId > 0 ? $variationId : TypeHelper::int(ArrayHelper::get($cartItem, 'product_id'), 0);
        $product = $productId > 0 ? ProductsRepository::get($productId) : null;
        $quantity = TypeHelper::float(ArrayHelper::get($cartItem, 'quantity'), 1);

        if (! $product) {
            return $removeFromCartLink;
        }

        $removeFromCartUrl = htmlspecialchars_decode(wc_get_cart_remove_url($cartItemKey), ENT_COMPAT);

        try {
            /** this will be used in {@see ScriptEventDataInterceptor::addProductRemovedFromCartToRedirectUrl()} */
            $removeFromCartUrl = Url::fromString($removeFromCartUrl)
                // the following are removed first in the eventuality the product has been removed immediately after having been added
                ->removeQueryParameter(static::PRODUCT_ADDED_TO_CART_KEY)
                ->removeQueryParameter(static::PRODUCT_QUANTITY_KEY)
                ->addQueryParameter('remove_product_id', $productId)
                ->addQueryParameter(static::PRODUCT_QUANTITY_KEY, $quantity)
                ->toString();
        } catch (Exception $exception) {
            // since we are in a hook callback context, we catch the exception instead of throwing it
        }

        return sprintf(
            '<a href="%s" class="remove remove_from_cart_button" aria-label="%s" data-product_id="%s" data-cart_item_key="%s" data-product_sku="%s" data-product_name="%s" data-product_price="%s" data-product_quantity="%s" data-google_product_id="%s" data-total_amount="%s">&times;</a>',
            esc_url($removeFromCartUrl),
            esc_html__('Remove this item', 'woocommerce'),
            esc_attr((string) $productId),
            esc_attr($cartItemKey),
            esc_attr($product->get_sku()),
            esc_attr($product->get_name()),
            esc_attr($product->get_price()),
            esc_attr((string) $quantity),
            esc_attr(TypeHelper::string($product->get_meta(ProductAdapter::MARKETPLACES_GOOGLE_PRODUCT_ID), '')),
            esc_attr((string) $cart->get_total('edit'))
        );
    }

    /**
     * Adds a query argument to the URL WooCommerce redirects to after removing a product from the cart.
     *
     * @internal
     * @see WC_Form_Handler::update_cart_action()
     * @see wp_redirect()
     *
     * @param string|mixed $redirectToUrl
     * @return string|mixed
     */
    public function addProductRemovedFromCartToRedirectUrl($redirectToUrl)
    {
        if (! is_string($redirectToUrl)) {
            return $redirectToUrl;
        }

        try {
            $updatedRedirectToUrl = Url::fromString($redirectToUrl);
            $removedItemFlag = TypeHelper::int($updatedRedirectToUrl->getQueryParameter('removed_item'), 0);
            $originalRefererUrl = Url::fromString(TypeHelper::string(ArrayHelper::get($_SERVER, 'REQUEST_URI'), ''));
            $productId = TypeHelper::int($originalRefererUrl->getQueryParameter('remove_product_id'), 0);
            $quantity = TypeHelper::float($originalRefererUrl->getQueryParameter(static::PRODUCT_QUANTITY_KEY), 1);

            if (1 !== $removedItemFlag || $productId <= 0) {
                return $redirectToUrl;
            }

            $product = ProductsRepository::get($productId);

            if (! $product) {
                return $redirectToUrl;
            }

            return $updatedRedirectToUrl
                // the following are removed first in the eventuality the product has been removed immediately after having been added
                ->removeQueryParameter(static::PRODUCT_ADDED_TO_CART_KEY)
                ->removeQueryParameter(static::PRODUCT_QUANTITY_KEY)
                ->addQueryParameter(static::PRODUCT_REMOVED_FROM_CART_KEY, $product->get_id())
                ->addQueryParameter(static::PRODUCT_QUANTITY_KEY, $quantity)
                ->toString();
        } catch (Exception $exception) {
            return $redirectToUrl;
        }
    }
}
