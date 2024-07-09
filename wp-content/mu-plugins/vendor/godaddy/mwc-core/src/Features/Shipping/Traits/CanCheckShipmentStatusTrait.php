<?php

namespace GoDaddy\WordPress\MWC\Core\Features\Shipping\Traits;

use GoDaddy\WordPress\MWC\Common\Helpers\ArrayHelper;
use GoDaddy\WordPress\MWC\Shipping\Contracts\PackageStatusContract;
use GoDaddy\WordPress\MWC\Shipping\Contracts\ShipmentContract;
use GoDaddy\WordPress\MWC\Shipping\Models\Packages\Statuses\CreatedPackageStatus;

trait CanCheckShipmentStatusTrait
{
    /**
     * Determines if given shipment has a package in the "created" status.
     *
     * @param ShipmentContract $shipment
     * @return bool
     */
    protected function isShipmentInCreatedState(ShipmentContract $shipment) : bool
    {
        return $this->shipmentHasPackagesWithStatus($shipment, CreatedPackageStatus::class);
    }

    /**
     * Determines if the given shipment has a package with one of the specified statuses.
     *
     * @param ShipmentContract $shipment
     * @param class-string<PackageStatusContract> ...$statuses
     * @return bool
     */
    protected function shipmentHasPackagesWithStatus(ShipmentContract $shipment, string ...$statuses) : bool
    {
        foreach ($shipment->getPackages() as $package) {
            if (ArrayHelper::contains($statuses, get_class($package->getStatus()))) {
                return true;
            }
        }

        return false;
    }
}
