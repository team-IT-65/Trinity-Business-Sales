<?php

namespace GoDaddy\WordPress\MWC\Core\Analytics\Adapters;

use GoDaddy\WordPress\MWC\Common\DataSources\WooCommerce\Adapters\CurrencyAmountAdapter;
use GoDaddy\WordPress\MWC\Common\Helpers\TypeHelper;
use GoDaddy\WordPress\MWC\Common\Repositories\WooCommerceRepository;
use GoDaddy\WordPress\MWC\Common\Traits\CanGetNewInstanceTrait;
use GoDaddy\WordPress\MWC\Core\Analytics\Adapters\Contracts\AnalyticsEventAdapterContract;
use GoDaddy\WordPress\MWC\Core\Analytics\Adapters\Traits\ConvertsProductsFromSourceTrait;
use GoDaddy\WordPress\MWC\Core\Analytics\DataObjects\EventData;
use WC_Product;

/**
 * Adapts data from a product event into normalized event data.
 *
 * @method static static getNewInstance(WC_Product $source, float $quantity = 1)
 */
class ProductEventDataAdapter implements AnalyticsEventAdapterContract
{
    use CanGetNewInstanceTrait;
    use ConvertsProductsFromSourceTrait;

    /** @var WC_Product */
    protected WC_Product $source;

    /** @var float */
    protected float $quantity;

    /**
     * Constructor.
     *
     * @param WC_Product $wcProduct
     * @param float $quantity Default 1
     */
    public function __construct(WC_Product $wcProduct, float $quantity = 1)
    {
        $this->source = $wcProduct;
        $this->quantity = $quantity;
    }

    /**
     * Converts a WC_Product to an {@see EventData} object.
     *
     * @return EventData
     */
    public function convertFromSource() : EventData
    {
        $currency = WooCommerceRepository::getCurrency();

        return EventData::getNewInstance()
            ->setProducts([$this->convertProductFromSource($this->source, $currency, $this->quantity)])
            ->setTotalAmount((new CurrencyAmountAdapter(TypeHelper::float($this->source->get_price(), 0.0), $currency))->convertFromSource());
    }

    /**
     * {@inerhitDoc}.
     */
    public function convertToSource()
    {
        // not implemented
        return null;
    }
}
