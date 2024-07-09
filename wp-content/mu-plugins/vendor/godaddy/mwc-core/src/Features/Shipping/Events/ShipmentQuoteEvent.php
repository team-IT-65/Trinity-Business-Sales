<?php

namespace GoDaddy\WordPress\MWC\Core\Features\Shipping\Events;

use Exception;
use GoDaddy\WordPress\MWC\Common\Events\ModelEvent;
use GoDaddy\WordPress\MWC\Common\Helpers\ArrayHelper;
use GoDaddy\WordPress\MWC\Shipping\Models\Contracts\ShippingRateContract;
use WC_Product;

class ShipmentQuoteEvent extends ModelEvent
{
    /** @var ShippingRateContract[] */
    protected $shippingRates;

    /**
     * Gets shipping rates.
     *
     * @return ShippingRateContract[]
     */
    public function getShippingRates() : array
    {
        return $this->shippingRates;
    }

    /**
     * Sets shipping rates.
     *
     * @param ShippingRateContract ...$value
     * @return $this
     */
    public function setShippingRates(ShippingRateContract ...$value) : ShipmentQuoteEvent
    {
        $this->shippingRates = $value;

        return $this;
    }

    /**
     * Builds the initial data for the event.
     *
     * @return array<string, mixed>
     */
    protected function buildInitialData() : array
    {
        try {
            return ArrayHelper::combine($this->replaceWooCommerceProductObjectsWithIds(parent::buildInitialData()), $this->getShippingRatesData());
        } catch (Exception $exception) {
            return parent::buildInitialData();
        }
    }

    /**
     * Removes {@see WC_Product} instances from the event data and replaces them with the corresponding product ID.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    protected function replaceWooCommerceProductObjectsWithIds(array $data) : array
    {
        foreach (ArrayHelper::wrap(ArrayHelper::get($data, 'resource.packages')) as $packageId => $packageData) {
            foreach (ArrayHelper::wrap(ArrayHelper::get($packageData, 'items', [])) as $itemIndex => $itemData) {
                $product = ArrayHelper::get($itemData, 'product');

                ArrayHelper::set(
                    $data,
                    "resource.packages.{$packageId}.items.{$itemIndex}.product",
                    $product instanceof WC_Product ? ['id' => $product->get_id()] : null
                );
            }
        }

        return $data;
    }

    /**
     * Gets shipping rates data for this event.
     *
     * @return array<string, mixed>
     */
    protected function getShippingRatesData() : array
    {
        return [
            'rates' => array_map(static function (ShippingRateContract $shippingRate) {
                return $shippingRate->toArray();
            }, $this->getShippingRates()),
        ];
    }
}
