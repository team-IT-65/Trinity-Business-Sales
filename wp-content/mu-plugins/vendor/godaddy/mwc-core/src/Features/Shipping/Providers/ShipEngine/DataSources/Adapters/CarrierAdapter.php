<?php

namespace GoDaddy\WordPress\MWC\Core\Features\Shipping\Providers\ShipEngine\DataSources\Adapters;

use BadMethodCallException;
use GoDaddy\WordPress\MWC\Common\DataSources\Contracts\DataSourceAdapterContract;
use GoDaddy\WordPress\MWC\Common\Helpers\ArrayHelper;
use GoDaddy\WordPress\MWC\Common\Traits\CanGetNewInstanceTrait;
use GoDaddy\WordPress\MWC\Shipping\Models\Carrier;
use GoDaddy\WordPress\MWC\Shipping\Models\Contracts\CarrierContract;
use GoDaddy\WordPress\MWC\Shipping\Models\Contracts\PackageTypeContract;

class CarrierAdapter implements DataSourceAdapterContract
{
    use CanGetNewInstanceTrait;

    /**
     * @var array<string, mixed>
     */
    protected array $source;

    /**
     * Constructor.
     *
     * @param array<string, mixed> $source
     */
    public function __construct(array $source)
    {
        $this->source = $source;
    }

    /**
     * Converts an array of data into a {@see CarrierContract} instance.
     *
     * {@inheritDoc}
     */
    public function convertFromSource() : CarrierContract
    {
        return Carrier::getNewInstance()
            ->setId(ArrayHelper::getStringValueForKey($this->source, 'carrier_id'))
            ->setName(ArrayHelper::getStringValueForKey($this->source, 'carrier_code'))
            ->setLabel($this->getLabelFromSource())
            ->setPackages($this->getPackageTypesFromSource());
    }

    /**
     * Converts a {@see CarrierContract} object into an array of data.
     *
     * Not implemented.
     *
     * @param CarrierContract|null $carrier
     *
     * @return array<string, mixed>
     * @throws BadMethodCallException
     */
    public function convertToSource(?CarrierContract $carrier = null) : array
    {
        throw new BadMethodCallException('Not implemented.');
    }

    /**
     * Converts an array of data into a {@see PackageTypeContract} instance.
     *
     * @param array<string, mixed> $data
     *
     * @return PackageTypeContract|null
     */
    protected function getPackageTypeFromSource(array $data) : ?PackageTypeContract
    {
        return PackageTypeAdapter::getNewInstance($data)->convertFromSource();
    }

    /**
     * Converts an array of data into an array of {@see PackageTypeContract} instances.
     *
     * @return PackageTypeContract[]
     */
    protected function getPackageTypesFromSource() : array
    {
        if (! $packages = ArrayHelper::getArrayValueForKey($this->source, 'packages')) {
            return [];
        }

        return array_values(array_filter(array_map([$this, 'getPackageTypeFromSource'], $packages)));
    }

    /**
     * Gets the label of the carrier from the source data.
     *
     * @return string
     */
    protected function getLabelFromSource() : string
    {
        return ArrayHelper::getStringValueForKey($this->source, 'friendly_name') ?:
            ArrayHelper::getStringValueForKey($this->source, 'carrier_friendly_name');
    }
}
