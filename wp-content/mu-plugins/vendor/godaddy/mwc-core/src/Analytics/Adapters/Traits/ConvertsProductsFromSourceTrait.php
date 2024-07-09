<?php

namespace GoDaddy\WordPress\MWC\Core\Analytics\Adapters\Traits;

use GoDaddy\WordPress\MWC\Common\DataSources\WooCommerce\Adapters\CurrencyAmountAdapter;
use GoDaddy\WordPress\MWC\Common\Helpers\TypeHelper;
use GoDaddy\WordPress\MWC\Core\Analytics\DataObjects\Product;
use WC_Product;

/**
 * Trait for converting WC_Product objects from source.
 */
trait ConvertsProductsFromSourceTrait
{
    /**
     * Converts a WC_Product object into a {@see Product} data object.
     *
     * @NOTE instead of using the main {@see ProductAdapter} we duplicate some of its logic below to limit to what we need to feed to the {@see EventData}. This is because the event runs during critical front-end page loads and we're trying to limit how much unnecessary data we process + build up. {unfulvio 2022-11-02}
     *
     * @param WC_Product $wcProduct
     * @param string $currency
     * @param float $quantity
     * @return Product
     */
    protected function convertProductFromSource(WC_Product $wcProduct, string $currency, float $quantity = 1) : Product
    {
        /* @phpstan-ignore-next-line */
        return Product::getNewInstance()
            ->setId(TypeHelper::int($wcProduct->get_id(), 0))
            ->setQuantity($quantity)
            ->setName(TypeHelper::string($wcProduct->get_name(), ''))
            ->setSku(TypeHelper::string($wcProduct->get_sku(), ''))
            ->setRegularPrice((new CurrencyAmountAdapter(TypeHelper::float($wcProduct->get_regular_price(), 0.0), $currency))->convertFromSource())
            ->setSalePrice((new CurrencyAmountAdapter(TypeHelper::float($wcProduct->get_sale_price(), 0.0), $currency))->convertFromSource());
    }
}
