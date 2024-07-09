<?php

namespace GoDaddy\WordPress\MWC\Core\Analytics\DataObjects;

use GoDaddy\WordPress\MWC\Common\DataSources\WooCommerce\Adapters\CurrencyAmountAdapter;
use GoDaddy\WordPress\MWC\Common\Models\CurrencyAmount;
use GoDaddy\WordPress\MWC\Common\Traits\CanGetNewInstanceTrait;

/**
 * Data transfer object for the data associated with an analytics event.
 */
class EventData
{
    use CanGetNewInstanceTrait;

    /** @var Product[] products associated with this event */
    protected array $products = [];

    /** @var CurrencyAmount|null total value of the cart or order */
    protected ?CurrencyAmount $totalAmount = null;

    /** @var CurrencyAmount|null tax amount for the cart or order */
    protected ?CurrencyAmount $taxAmount = null;

    /** @var CurrencyAmount|null shipping amount for the cart or order */
    protected ?CurrencyAmount $shippingAmount = null;

    /**
     * Gets the products.
     *
     * @return Product[]
     */
    public function getProducts() : array
    {
        return $this->products;
    }

    /**
     * Gets the total value of the cart/order/etc.
     *
     * @return CurrencyAmount|null
     */
    public function getTotalAmount() : ?CurrencyAmount
    {
        return $this->totalAmount;
    }

    /**
     * Gets the tax amount for the cart/order/etc.
     *
     * @return CurrencyAmount|null
     */
    public function getTaxAmount() : ?CurrencyAmount
    {
        return $this->taxAmount;
    }

    /**
     * Gets the shipping amount for the cart/order/etc.
     *
     * @return CurrencyAmount|null
     */
    public function getShippingAmount() : ?CurrencyAmount
    {
        return $this->shippingAmount;
    }

    /**
     * Sets the products.
     *
     * @param Product[] $value
     * @return $this
     */
    public function setProducts(array $value) : EventData
    {
        $this->products = $value;

        return $this;
    }

    /**
     * Sets the total amount.
     *
     * @param CurrencyAmount|null $value
     * @return $this
     */
    public function setTotalAmount(?CurrencyAmount $value) : EventData
    {
        $this->totalAmount = $value;

        return $this;
    }

    /**
     * Sets the tax amount.
     *
     * @param CurrencyAmount|null $value
     * @return $this
     */
    public function setTaxAmount(?CurrencyAmount $value) : EventData
    {
        $this->taxAmount = $value;

        return $this;
    }

    /**
     * Sets the shipping amount.
     *
     * @param CurrencyAmount|null $value
     * @return $this
     */
    public function setShippingAmount(?CurrencyAmount $value) : EventData
    {
        $this->shippingAmount = $value;

        return $this;
    }

    /**
     * Converts the object to array form.
     *
     * @return array<string,mixed>
     */
    public function toArray() : array
    {
        $amountAdapter = new CurrencyAmountAdapter(0, '');

        return [
            'products'       => $this->getProductsToArray(),
            'totalAmount'    => $amountAdapter->convertToSource($this->getTotalAmount()),
            'taxAmount'      => $amountAdapter->convertToSource($this->getTaxAmount()),
            'shippingAmount' => $amountAdapter->convertToSource($this->getShippingAmount()),
        ];
    }

    /**
     * Gets the products as arrays of data.
     *
     * @return array<array<string, mixed>>
     */
    protected function getProductsToArray() : array
    {
        return array_map(function (Product $product) {
            return $product->toArray();
        }, $this->getProducts());
    }
}
