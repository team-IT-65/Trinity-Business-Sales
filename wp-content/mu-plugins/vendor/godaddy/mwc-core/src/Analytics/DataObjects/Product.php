<?php

namespace GoDaddy\WordPress\MWC\Core\Analytics\DataObjects;

use GoDaddy\WordPress\MWC\Common\DataSources\WooCommerce\Adapters\CurrencyAmountAdapter;
use GoDaddy\WordPress\MWC\Core\WooCommerce\Models\Products\Product as CoreProduct;

/**
 * Represents a single product within an analytics event.
 */
class Product extends CoreProduct
{
    /** @var float quantity of this item */
    protected float $quantity = 1.0;

    /**
     * Gets the quantity of this item.
     *
     * @return float
     */
    public function getQuantity() : float
    {
        return $this->quantity;
    }

    /**
     * Sets the item quantity.
     *
     * @param float $value
     * @return $this
     */
    public function setQuantity(float $value) : Product
    {
        $this->quantity = $value;

        return $this;
    }

    /**
     * Converts the object to array form.
     *
     * @return array<string, mixed>
     */
    public function toArray() : array
    {
        return [
            'id'              => $this->getId(),
            'sku'             => $this->getSku(),
            'price'           => (new CurrencyAmountAdapter(0, ''))->convertToSource($this->getRegularPrice()),
            'name'            => $this->getName(),
            'quantity'        => $this->getQuantity(),
            'googleProductId' => $this->getMarketplacesGoogleProductId(),
        ];
    }
}
