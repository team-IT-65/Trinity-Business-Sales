<?php

namespace GoDaddy\WordPress\MWC\Core\Analytics\Adapters;

use GoDaddy\WordPress\MWC\Common\Exceptions\AdapterException;
use GoDaddy\WordPress\MWC\Common\Repositories\WooCommerceRepository;
use GoDaddy\WordPress\MWC\Common\Traits\CanGetNewInstanceTrait;
use GoDaddy\WordPress\MWC\Core\Analytics\Adapters\Contracts\AnalyticsEventAdapterContract;
use GoDaddy\WordPress\MWC\Core\Analytics\Adapters\Traits\ConvertsProductsFromSourceTrait;
use GoDaddy\WordPress\MWC\Core\Analytics\DataObjects\EventData;
use GoDaddy\WordPress\MWC\Core\Analytics\DataObjects\Product;
use GoDaddy\WordPress\MWC\Core\WooCommerce\Adapters\OrderAdapter;
use GoDaddy\WordPress\MWC\Core\WooCommerce\Models\Orders\Order;
use WC_Order;

/**
 * Order event adapter class.
 *
 * @method static static getNewInstance(WC_Order $wcOrder)
 */
class OrderEventAdapter implements AnalyticsEventAdapterContract
{
    use CanGetNewInstanceTrait;
    use ConvertsProductsFromSourceTrait;

    /** @var WC_Order */
    protected WC_Order $source;

    /**
     * Constructor.
     *
     * @param WC_Order $wcOrder
     */
    public function __construct(WC_Order $wcOrder)
    {
        $this->source = $wcOrder;
    }

    /**
     * Converts a WC_Order object to an {@see EventData} object.
     *
     * @throws AdapterException
     */
    public function convertFromSource() : EventData
    {
        $coreOrder = OrderAdapter::getNewInstance($this->source)->convertFromSource();

        return EventData::getNewInstance()
            ->setTotalAmount($coreOrder->getTotalAmount())
            ->setTaxAmount($coreOrder->getTaxAmount())
            ->setShippingAmount($coreOrder->getShippingAmount())
            ->setProducts($this->convertProducts($coreOrder));
    }

    /**
     * Converts the orders line items into product objects.
     *
     * @param Order $order
     * @return Product[]
     */
    protected function convertProducts(Order $order) : array
    {
        $currency = WooCommerceRepository::getCurrency();
        $products = [];

        foreach ($order->getLineItems() as $lineItem) {
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
