<?php

namespace GoDaddy\WordPress\MWC\Core\Features\Shipping\Services;

use GoDaddy\WordPress\MWC\Common\Events\Events;
use GoDaddy\WordPress\MWC\Common\Exceptions\BaseException;
use GoDaddy\WordPress\MWC\Core\Features\Shipping\Contracts\ShippingProviderContract;
use GoDaddy\WordPress\MWC\Core\Features\Shipping\Events\ShipmentQuoteEvent;
use GoDaddy\WordPress\MWC\Core\Features\Shipping\Services\Contracts\ShipmentTrackingServiceContract;
use GoDaddy\WordPress\MWC\Core\Features\Shipping\Services\Contracts\ShippingLabelsServiceContract;
use GoDaddy\WordPress\MWC\Core\Features\Shipping\Services\Traits\HasShippingProviderTrait;
use GoDaddy\WordPress\MWC\Dashboard\Exceptions\ShipmentNotFoundException;
use GoDaddy\WordPress\MWC\Shipping\Contracts\CalculateShippingRatesOperationContract;
use GoDaddy\WordPress\MWC\Shipping\Contracts\GetShippingRateOperationContract;
use GoDaddy\WordPress\MWC\Shipping\Contracts\GetTrackingStatusOperationContract;
use GoDaddy\WordPress\MWC\Shipping\Contracts\HasShipmentContract;
use GoDaddy\WordPress\MWC\Shipping\Contracts\ListCarriersOperationContract;
use GoDaddy\WordPress\MWC\Shipping\Contracts\PurchaseShippingLabelsOperationContract;
use GoDaddy\WordPress\MWC\Shipping\Contracts\VoidShippingLabelOperationContract;
use GoDaddy\WordPress\MWC\Shipping\Operations\GetTrackingStatusOperation;

class ShippingLabelsService implements ShippingLabelsServiceContract
{
    use HasShippingProviderTrait;

    /** @var ShipmentTrackingServiceContract */
    protected $shipmentTrackingService;

    final public function __construct(
        ShipmentTrackingServiceContract $shipmentTrackingService,
        ShippingProviderContract $shippingProvider
    ) {
        $this->shipmentTrackingService = $shipmentTrackingService;
        $this->shippingProvider = $shippingProvider;
    }

    /**
     * {@inheritDoc}
     */
    public static function for(int $orderId)
    {
        return new static(
            ShipmentTrackingServiceFactory::getNewInstance()->getShipmentTrackingService($orderId),
            static::getShippingProviderInstance()
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getCarriers(ListCarriersOperationContract $operation) : ListCarriersOperationContract
    {
        return $this->shippingProvider->carriers()->list($operation);
    }

    /** {@inheritDoc} */
    public function getShippingRate(GetShippingRateOperationContract $operation) : GetShippingRateOperationContract
    {
        return $this->shippingProvider->rates()->get($operation);
    }

    /**
     * {@inheritDoc}
     */
    public function calculateShippingRates(CalculateShippingRatesOperationContract $operation) : CalculateShippingRatesOperationContract
    {
        $operation = $this->shippingProvider->rates()->calculate($operation);

        $this->shipmentTrackingService->addShipments($operation->getShipment());

        Events::broadcast($this->getShipmentQuoteEvent($operation));

        return $operation;
    }

    /**
     * Gets a {@see ShipmentQuoteEvent} instance for the shipment and shipping rates included in the given operation.
     *
     * @param CalculateShippingRatesOperationContract $operation
     * @return ShipmentQuoteEvent
     */
    protected function getShipmentQuoteEvent(CalculateShippingRatesOperationContract $operation) : ShipmentQuoteEvent
    {
        return (new ShipmentQuoteEvent($operation->getShipment(), 'shipment', 'quote'))
            ->setShippingRates(...$operation->getShippingRates());
    }

    /** {@inheritDoc} */
    public function purchaseShippingLabels(PurchaseShippingLabelsOperationContract $operation) : PurchaseShippingLabelsOperationContract
    {
        $operation = $this->shippingProvider->labels()->purchase($operation);

        $operation->getShipment()
            ->setProviderName($this->shippingProvider->getName())
            ->setProviderLabel($this->shippingProvider->getLabel());

        $this->updateTrackingUrl($operation);

        $this->updateShipment($operation);

        return $operation;
    }

    /** {@inheritDoc} */
    public function voidShippingLabel(VoidShippingLabelOperationContract $operation) : VoidShippingLabelOperationContract
    {
        $operation = $this->shippingProvider->labels()->void($operation);

        $this->updateShipment($operation->getPackage());

        return $operation;
    }

    /** {@inheritDoc} */
    public function getTrackingStatus(GetTrackingStatusOperationContract $operation) : GetTrackingStatusOperationContract
    {
        return $this->shippingProvider->tracking()->status($operation);
    }

    /**
     * Updates a shipment, and the fulfillment status.
     *
     * @param HasShipmentContract $contract
     *
     * @return void
     * @throws ShipmentNotFoundException
     * @throws BaseException
     */
    protected function updateShipment(HasShipmentContract $contract) : void
    {
        $this->shipmentTrackingService
            ->updateShipment($contract->getShipment()->getId(), $contract->getShipment());
    }

    /**
     * Gets the trackingUrl and updates the Shipment.
     *
     * @param PurchaseShippingLabelsOperationContract $operation
     *
     * @return void
     */
    protected function updateTrackingUrl(PurchaseShippingLabelsOperationContract $operation) : void
    {
        $packages = array_values($operation->getShipment()->getPackages());

        if (! $package = array_shift($packages)) {
            return;
        }

        $trackingStatusOperation = (new GetTrackingStatusOperation())
            ->setAccount($operation->getAccount())
            ->setPackage($package);

        $updatedTrackingStatusOperation = $this->getTrackingStatus($trackingStatusOperation);

        if ($trackingUrl = $updatedTrackingStatusOperation->getTrackingUrl()) {
            $package->setTrackingUrl($trackingUrl);
        }
    }
}
