<?php

namespace GoDaddy\WordPress\MWC\Core\Features\Shipping\Providers\ShipEngine\DataSources\Adapters;

use BadMethodCallException;
use GoDaddy\WordPress\MWC\Common\DataSources\Contracts\DataSourceAdapterContract;
use GoDaddy\WordPress\MWC\Common\DataSources\WooCommerce\Adapters\CurrencyAmountAdapter;
use GoDaddy\WordPress\MWC\Common\Helpers\ArrayHelper;
use GoDaddy\WordPress\MWC\Common\Models\CurrencyAmount;
use GoDaddy\WordPress\MWC\Common\Repositories\WooCommerceRepository;
use GoDaddy\WordPress\MWC\Common\Traits\CanGetNewInstanceTrait;
use GoDaddy\WordPress\MWC\Shipping\Contracts\ShippingServiceContract;
use GoDaddy\WordPress\MWC\Shipping\Models\Contracts\CarrierContract;
use GoDaddy\WordPress\MWC\Shipping\Models\Contracts\PackageTypeContract;
use GoDaddy\WordPress\MWC\Shipping\Models\Contracts\ShippingRateContract;
use GoDaddy\WordPress\MWC\Shipping\Models\Contracts\ShippingRateItemContract;
use GoDaddy\WordPress\MWC\Shipping\Models\ShippingRate;
use GoDaddy\WordPress\MWC\Shipping\Models\ShippingRateItem;
use GoDaddy\WordPress\MWC\Shipping\Models\ShippingService;

class ShippingRateAdapter implements DataSourceAdapterContract
{
    use CanGetNewInstanceTrait;

    /** @var array<string, mixed> */
    protected array $source;

    /** @var array<string, CarrierContract> */
    protected array $carriers;

    /**
     * @param array<string, mixed> $source
     */
    public function __construct(array $source)
    {
        $this->source = $source;
    }

    /**
     * Converts an array of data into a {@see ShippingRateContract} instance.
     *
     * @return ShippingRateContract
     */
    public function convertFromSource() : ShippingRateContract
    {
        $items = $this->convertItemsFromSource();
        $carrier = $this->convertCarrierFromSource();

        return (new ShippingRate())
            ->setId(ArrayHelper::getStringValueForKey($this->source, 'rate_id'))
            ->setRemoteId(ArrayHelper::getStringValueForKey($this->source, 'rate_id'))
            ->setService($this->convertServiceFromSource())
            ->setCarrier($carrier)
            ->setPackageType($this->getPackageTypeFromSource($carrier))
            ->addItems(...$items)
            ->setIsTrackable((bool) ArrayHelper::get($this->source, 'trackable', false))
            ->setDeliveryDays((int) ArrayHelper::get($this->source, 'delivery_days', 0))
            ->setTotal($this->calculateTotal($items));
    }

    /**
     * Converts the source data into an array of {@see ShippingRateItemContract} instances.
     *
     * @return ShippingRateItemContract[]
     */
    protected function convertItemsFromSource() : array
    {
        $items = [];

        if ($data = ArrayHelper::get($this->source, 'shipping_amount')) {
            $items[] = $this->convertItemFromSource('shipping_amount', 'Shipping Amount', $data);
        }

        if ($data = ArrayHelper::get($this->source, 'insurance_amount')) {
            $items[] = $this->convertItemFromSource('insurance_amount', 'Insurance Amount', $data);
        }

        if ($data = ArrayHelper::get($this->source, 'confirmation_amount')) {
            $items[] = $this->convertItemFromSource('confirmation_amount', 'Confirmation Amount', $data);
        }

        if ($data = ArrayHelper::get($this->source, 'tax_amount')) {
            $items[] = $this->convertItemFromSource('tax_amount', 'Tax Amount', $data);
        }

        if ($data = ArrayHelper::get($this->source, 'other_amount')) {
            $items[] = $this->convertItemFromSource('other_amount', 'Other Amount', $data);
        }

        return $items;
    }

    /**
     * Converts the given data into an instance of {@see ShippingRateItemContract}.
     *
     * @param string $name
     * @param string $label
     * @param array{currency: string, amount: int|float} $data
     * @return ShippingRateItemContract
     */
    protected function convertItemFromSource(string $name, string $label, array $data) : ShippingRateItemContract
    {
        $price = (new CurrencyAmountAdapter(
            $this->getFloatValue($data, 'amount'),
            ArrayHelper::getStringValueForKey($data, 'currency')
        ))->convertFromSource();

        return (new ShippingRateItem())
            ->setName($name)
            ->setLabel($label)
            ->setPrice($price);
    }

    /**
     * Gets a float value from the given array.
     *
     * Returns an 0.0 if the value cannot be converted to float.
     *
     * @param array<string, mixed> $stored
     * @param string $key
     * @return float
     */
    protected function getFloatValue(array $stored, string $key) : float
    {
        $value = ArrayHelper::get($stored, $key);

        return is_numeric($value) ? (float) $value : 0.0;
    }

    /**
     * Converts the source data into an instance of {@see ShippingServiceContract}.
     *
     * @return ShippingServiceContract
     */
    protected function convertServiceFromSource() : ShippingServiceContract
    {
        return (new ShippingService())
            ->setName(ArrayHelper::getStringValueForKey($this->source, 'service_code'))
            ->setLabel(ArrayHelper::getStringValueForKey($this->source, 'service_type'));
    }

    /**
     * Converts the source data into an instance of {@see CarrierContract}.
     *
     * @return CarrierContract
     */
    protected function convertCarrierFromSource() : CarrierContract
    {
        return $this->getCarrierByIdFromSource() ?? CarrierAdapter::getNewInstance($this->source)->convertFromSource();
    }

    /**
     * Get matching {@see CarrierContract} instance based on `carrier_id` from the source data.
     *
     * @return CarrierContract|null
     */
    protected function getCarrierByIdFromSource() : ?CarrierContract
    {
        if (! $carrierId = ArrayHelper::getStringValueForKey($this->source, 'carrier_id')) {
            return null;
        }

        return $this->getCarrierById($carrierId);
    }

    /**
     * Calculates the total cost of the shipping rate using the price of the given shipping rate items.
     *
     * @param ShippingRateItemContract[] $items
     * @return CurrencyAmount
     */
    protected function calculateTotal(array $items) : CurrencyAmount
    {
        $currencyCode = null;
        $total = 0;

        foreach ($items as $item) {
            if (! $currencyCode) {
                $currencyCode = $item->getPrice()->getCurrencyCode();
            }

            $total += $item->getPrice()->getAmount();
        }

        return (new CurrencyAmount())
            ->setAmount($total)
            ->setCurrencyCode($currencyCode ?? WooCommerceRepository::getCurrency());
    }

    /**
     * Converts a {@see ShippingRateContract} object into an array of data.
     *
     * Not implemented.
     *
     * @param ShippingRateContract|null $shippingRate
     * @return array<string, mixed>
     * @throws BadMethodCallException
     */
    public function convertToSource(?ShippingRateContract $shippingRate = null) : array
    {
        throw new BadMethodCallException('Not implemented.');
    }

    /**
     * Index given carriers by their IDs.
     *
     * @param CarrierContract[] $carriers
     *
     * @return array<string, CarrierContract>
     */
    protected function indexCarriersById(array $carriers) : array
    {
        return ArrayHelper::indexBy($carriers, static fn (CarrierContract $carrier) => $carrier->getId());
    }

    /**
     * Sets the carriers’ property value.
     *
     * @param CarrierContract[] $carriers
     *
     * @return $this
     */
    public function setCarriers(array $carriers) : ShippingRateAdapter
    {
        $this->carriers = $this->indexCarriersById($carriers);

        return $this;
    }

    /**
     * Gets carrier by the given ID.
     *
     * @param string $id
     *
     * @return CarrierContract|null
     */
    protected function getCarrierById(string $id) : ?CarrierContract
    {
        return $this->carriers[$id] ?? null;
    }

    /**
     * Gets package type from source based on given carrier.
     *
     * @param CarrierContract $carrier
     *
     * @return PackageTypeContract|null
     */
    protected function getPackageTypeFromSource(CarrierContract $carrier) : ?PackageTypeContract
    {
        return $carrier->getPackageByCode(ArrayHelper::getStringValueForKey($this->source, 'package_type'));
    }
}
