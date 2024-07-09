<?php

namespace GoDaddy\WordPress\MWC\Core\Features\Shipping\Providers\ShipEngine\DataSources\Adapters;

use BadMethodCallException;
use GoDaddy\WordPress\MWC\Common\DataSources\Contracts\DataSourceAdapterContract;
use GoDaddy\WordPress\MWC\Common\Helpers\ArrayHelper;
use GoDaddy\WordPress\MWC\Common\Traits\CanGetNewInstanceTrait;
use GoDaddy\WordPress\MWC\Shipping\Models\Contracts\PackageType;
use GoDaddy\WordPress\MWC\Shipping\Models\Contracts\PackageTypeContract;

class PackageTypeAdapter implements DataSourceAdapterContract
{
    use CanGetNewInstanceTrait;

    /**
     * @var array <string, mixed>
     */
    protected array $source;

    /**
     * @param array<string, mixed> $source
     */
    public function __construct(array $source)
    {
        $this->source = $source;
    }

    /**
     * Converts an array of data into a {@see PackageTypeContract} instance.
     *
     * @return PackageTypeContract|null
     */
    public function convertFromSource() : ?PackageTypeContract
    {
        if (! $code = ArrayHelper::getStringValueForKey($this->source, 'package_code')) {
            return null;
        }

        return PackageType::seed([
            'code'        => $code,
            'name'        => ArrayHelper::getStringValueForKey($this->source, 'name'),
            'description' => ArrayHelper::getStringValueForKey($this->source, 'description'),
        ]);
    }

    /**
     * Converts a {@see PackageTypeContract} object into an array of data.
     *
     * Not implemented.
     *
     * @param PackageTypeContract|null $carrierPackage
     * @return array<string, mixed>
     * @throws BadMethodCallException
     */
    public function convertToSource(?PackageTypeContract $carrierPackage = null) : array
    {
        throw new BadMethodCallException('Not implemented.');
    }
}
