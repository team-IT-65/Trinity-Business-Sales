<?php

namespace GoDaddy\WordPress\MWC\Core\Analytics\Adapters;

use GoDaddy\WordPress\MWC\Common\DataSources\WooCommerce\Adapters\SessionValue\CartAdapter;
use GoDaddy\WordPress\MWC\Common\Exceptions\BaseException;
use GoDaddy\WordPress\MWC\Common\Models\Cart;
use GoDaddy\WordPress\MWC\Common\Repositories\WooCommerceRepository;
use GoDaddy\WordPress\MWC\Common\Traits\CanGetNewInstanceTrait;
use GoDaddy\WordPress\MWC\Core\Analytics\Adapters\Contracts\AnalyticsEventAdapterContract;
use GoDaddy\WordPress\MWC\Core\Analytics\Adapters\Traits\ConvertsProductsFromSourceTrait;
use GoDaddy\WordPress\MWC\Core\Analytics\DataObjects\EventData;
use GoDaddy\WordPress\MWC\Core\Analytics\DataObjects\Product;

/**
 * Adapts data from a checkout event into normalized event data.
 *
 * @method static static getNewInstance(array $sessionData)
 */
class CheckoutEventAdapter implements AnalyticsEventAdapterContract
{
    use CanGetNewInstanceTrait;
    use ConvertsProductsFromSourceTrait;

    /** @var array<string, mixed> */
    protected array $source;

    /**
     * Constructor.
     *
     * @param array<string, mixed> $sessionData
     */
    public function __construct(array $sessionData)
    {
        $this->source = $sessionData;
    }

    /**
     * Converts session data to an {@see EventData} object.
     *
     * @return EventData
     * @throws BaseException
     */
    public function convertFromSource() : EventData
    {
        $cart = CartAdapter::getNewInstance($this->source)->convertFromSource();

        return EventData::getNewInstance()
            ->setTotalAmount($cart->getTotalAmount())
            ->setTaxAmount($cart->getTaxAmount())
            ->setShippingAmount($cart->getShippingAmount())
            ->setProducts($this->convertProducts($cart));
    }

    /**
     * Converts cart line items into {@see Product} objects.
     *
     * @param Cart $cart
     * @return Product[]
     */
    protected function convertProducts(Cart $cart) : array
    {
        $currency = WooCommerceRepository::getCurrency();
        $products = [];

        foreach ($cart->getLineItems() as $lineItem) {
            if (! $wcProduct = $lineItem->getProduct()) {
                continue;
            }

            $products[] = $this->convertProductFromSource($wcProduct, $currency, $lineItem->getQuantity());
        }

        return $products;
    }

    /**
     * {@inheritDoc}
     */
    public function convertToSource()
    {
        // not implemented
        return null;
    }
}
