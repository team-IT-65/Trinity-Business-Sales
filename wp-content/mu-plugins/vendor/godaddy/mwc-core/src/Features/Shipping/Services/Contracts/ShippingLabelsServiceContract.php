<?php

namespace GoDaddy\WordPress\MWC\Core\Features\Shipping\Services\Contracts;

use GoDaddy\WordPress\MWC\Common\Exceptions\BaseException;
use GoDaddy\WordPress\MWC\Dashboard\Exceptions\OrderNotFoundException;
use GoDaddy\WordPress\MWC\Dashboard\Exceptions\ShipmentNotFoundException;
use GoDaddy\WordPress\MWC\Dashboard\Exceptions\ShipmentValidationFailedException;
use GoDaddy\WordPress\MWC\Shipping\Contracts\CalculateShippingRatesOperationContract;
use GoDaddy\WordPress\MWC\Shipping\Contracts\GetShippingRateOperationContract;
use GoDaddy\WordPress\MWC\Shipping\Contracts\GetTrackingStatusOperationContract;
use GoDaddy\WordPress\MWC\Shipping\Contracts\ListCarriersOperationContract;
use GoDaddy\WordPress\MWC\Shipping\Contracts\PurchaseShippingLabelsOperationContract;
use GoDaddy\WordPress\MWC\Shipping\Contracts\VoidShippingLabelOperationContract;
use GoDaddy\WordPress\MWC\Shipping\Exceptions\Contracts\ShippingExceptionContract;

interface ShippingLabelsServiceContract
{
    /**
     * Gets an instance of the service for the Order identified with the given order ID.
     *
     * @param int $orderId
     *
     * @return static
     * @throws OrderNotFoundException
     */
    public static function for(int $orderId);

    /**
     * @param ListCarriersOperationContract $operation
     * @return ListCarriersOperationContract
     * @throws ShippingExceptionContract
     */
    public function getCarriers(ListCarriersOperationContract $operation) : ListCarriersOperationContract;

    /**
     * @param GetShippingRateOperationContract $operation
     *
     * @return GetShippingRateOperationContract
     */
    public function getShippingRate(GetShippingRateOperationContract $operation) : GetShippingRateOperationContract;

    /**
     * @param CalculateShippingRatesOperationContract $operation
     *
     * @return CalculateShippingRatesOperationContract
     * @throws ShipmentValidationFailedException
     */
    public function calculateShippingRates(CalculateShippingRatesOperationContract $operation) : CalculateShippingRatesOperationContract;

    /**
     * @param PurchaseShippingLabelsOperationContract $operation
     *
     * @return PurchaseShippingLabelsOperationContract
     * @throws ShipmentNotFoundException
     * @throws BaseException
     */
    public function purchaseShippingLabels(PurchaseShippingLabelsOperationContract $operation) : PurchaseShippingLabelsOperationContract;

    /**
     * Voids a shipping label using the information included in the given operation.
     *
     * @param VoidShippingLabelOperationContract $operation
     * @return VoidShippingLabelOperationContract
     * @throws ShipmentNotFoundException
     * @throws BaseException
     */
    public function voidShippingLabel(VoidShippingLabelOperationContract $operation) : VoidShippingLabelOperationContract;

    /**
     * @param GetTrackingStatusOperationContract $operation
     * @return GetTrackingStatusOperationContract
     */
    public function getTrackingStatus(GetTrackingStatusOperationContract $operation) : GetTrackingStatusOperationContract;
}
