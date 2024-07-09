<?php

namespace GoDaddy\WordPress\MWC\Core\Features\Shipping\API\Controllers\Orders\Shipments;

use Exception;
use GoDaddy\WordPress\MWC\Common\API\Response;
use GoDaddy\WordPress\MWC\Common\Exceptions\BaseException;
use GoDaddy\WordPress\MWC\Common\Helpers\ArrayHelper;
use GoDaddy\WordPress\MWC\Core\Features\Shipping\API\Controllers\AbstractController;
use GoDaddy\WordPress\MWC\Core\Features\Shipping\API\Traits\CanGetShippingAccountServiceTrait;
use GoDaddy\WordPress\MWC\Core\Features\Shipping\Services\Contracts\ShipmentTrackingServiceContract;
use GoDaddy\WordPress\MWC\Core\Features\Shipping\Services\Contracts\ShippingLabelsServiceContract;
use GoDaddy\WordPress\MWC\Core\Features\Shipping\Services\ShipmentTrackingServiceFactory;
use GoDaddy\WordPress\MWC\Core\Features\Shipping\Services\ShippingLabelsService;
use GoDaddy\WordPress\MWC\Dashboard\Exceptions\OrderNotFoundException;
use GoDaddy\WordPress\MWC\Dashboard\Exceptions\ShipmentNotFoundException;
use GoDaddy\WordPress\MWC\Dashboard\Shipping\DataSources\Request\Adapters\ShipmentAdapter;
use GoDaddy\WordPress\MWC\Shipping\Contracts\PackageContract;
use GoDaddy\WordPress\MWC\Shipping\Contracts\PurchaseShippingLabelsOperationContract;
use GoDaddy\WordPress\MWC\Shipping\Contracts\ShipmentContract;
use GoDaddy\WordPress\MWC\Shipping\Exceptions\Contracts\ShippingExceptionContract;
use GoDaddy\WordPress\MWC\Shipping\Exceptions\ShippingException;
use GoDaddy\WordPress\MWC\Shipping\Models\Contracts\AccountContract;
use GoDaddy\WordPress\MWC\Shipping\Models\Contracts\ShippingRateContract;
use GoDaddy\WordPress\MWC\Shipping\Models\ShippingRate;
use GoDaddy\WordPress\MWC\Shipping\Operations\GetShippingRateOperation;
use GoDaddy\WordPress\MWC\Shipping\Operations\PurchaseShippingLabelsOperation;
use WP_REST_Request;
use WP_REST_Response;

class ShippingLabelsController extends AbstractController
{
    use CanGetShippingAccountServiceTrait;

    /** @var string */
    protected $route = 'orders/(?P<orderId>[0-9]+)/shipments/(?P<shipmentId>[a-zA-Z0-9_-]+)/labels';

    /** @var ?ShippingLabelsServiceContract */
    protected $shippingLabelsService;

    /** @var ShipmentTrackingServiceContract */
    protected $shipmentTrackingService;

    /** {@inheritDoc} */
    public function registerRoutes() : void
    {
        register_rest_route($this->namespace, "/{$this->route}", [
            [
                'methods'             => 'POST',
                'callback'            => [$this, 'purchaseLabels'],
                'permission_callback' => [$this, 'createItemPermissionsCheck'],
            ],
            'args' => [
                'orderId' => [
                    'required'          => true,
                    'type'              => 'integer',
                    'validate_callback' => 'rest_validate_request_arg',
                    'sanitize_callback' => 'rest_sanitize_request_arg',
                ],
                'shipmentId' => [
                    'required'          => true,
                    'type'              => 'text',
                    'validate_callback' => 'rest_validate_request_arg',
                    'sanitize_callback' => 'rest_sanitize_request_arg',
                ],
            ],
            'schema' => [$this, 'getItemSchema'],
        ]);
    }

    /**
     * Gets an instance of {@see ShippingLabelsServiceContract}.
     *
     * @return ShippingLabelsServiceContract
     * @throws ShippingExceptionContract
     */
    protected function getShippingLabelsService() : ShippingLabelsServiceContract
    {
        if (! $this->shippingLabelsService) {
            throw new ShippingException('An instance of ShippingLabelsServiceContract is not available.');
        }

        return $this->shippingLabelsService;
    }

    /**
     * Sets the instance of {@see ShippingLabelsServiceContract}.
     *
     * @return $this
     */
    protected function setShippingLabelsService(ShippingLabelsServiceContract $value) : ShippingLabelsController
    {
        $this->shippingLabelsService = $value;

        return $this;
    }

    /**
     * Gets an instance of {@see ShipmentTrackingServiceContract}.
     *
     * @return ShipmentTrackingServiceContract
     * @throws ShippingException
     */
    protected function getShipmentTrackingService() : ShipmentTrackingServiceContract
    {
        if ($this->shipmentTrackingService === null) {
            throw new ShippingException('An instance of ShipmentTrackingServiceContract is not available.');
        }

        return $this->shipmentTrackingService;
    }

    /**
     * Sets the instance of {@see ShipmentTrackingServiceContract}.
     *
     * @return $this
     */
    protected function setShipmentTrackingService(ShipmentTrackingServiceContract $value) : ShippingLabelsController
    {
        $this->shipmentTrackingService = $value;

        return $this;
    }

    /**
     * Handles the POST orders/{orderId}/shipments/{shipmentId}/labels endpoint.
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function purchaseLabels(WP_REST_Request $request) : WP_REST_Response
    {
        try {
            return $this->processPurchaseShippingLabelsRequest($request);
        } catch (ShippingExceptionContract $exception) {
            return $this->getShippingExceptionErrorResponse($exception);
        } catch (Exception $exception) {
            return $this->getGenericExceptionErrorResponse($exception);
        }
    }

    /**
     * Processes the request to purchase shipping labels.
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     * @throws OrderNotFoundException
     * @throws ShipmentNotFoundException
     * @throws ShippingException
     * @throws ShippingExceptionContract
     * @throws BaseException
     */
    protected function processPurchaseShippingLabelsRequest(WP_REST_Request $request) : WP_REST_Response
    {
        $shipmentId = $request->get_param('shipmentId');

        $account = $this->getShippingAccountService()->getAccount();

        if (! $rateId = ArrayHelper::get($request->get_json_params(), 'rateId')) {
            throw new ShippingException('The rateId is required.');
        }

        $this->setShippingLabelsServiceFromRequest($request);

        $this->setShipmentTrackingServiceFromRequest($request);

        $shipment = $this->getShipmentWithShippingRate($shipmentId, $rateId, $account);

        $operation = $this->getPurchaseShippingLabelsOperation($shipment, $account);

        $operation = $this->getShippingLabelsService()->purchaseShippingLabels($operation);

        $shipmentResponse = $this->getShipmentData($operation->getShipment());
        $labelResponse = ArrayHelper::get($shipmentResponse, 'label');

        return $this->getWordPressResponse(
            (new Response())
                ->setBody([
                    'shipment' => $shipmentResponse,
                    'label'    => $labelResponse,
                ])
        );
    }

    /**
     * Sets the instance of {@see ShippingLabelsServiceContract} for the Order identified with the ID in the given request.
     *
     * @param WP_REST_Request $request
     * @return void
     * @throws OrderNotFoundException
     */
    protected function setShippingLabelsServiceFromRequest(WP_REST_Request $request) : void
    {
        $orderId = (int) $request->get_param('orderId');

        $this->setShippingLabelsService(ShippingLabelsService::for($orderId));
    }

    /**
     * Sets the instance of {@see ShipmentTrackingServiceContract} for the Order identified with the ID in the given request.
     *
     * @param WP_REST_Request $request
     * @return void
     * @throws OrderNotFoundException
     */
    protected function setShipmentTrackingServiceFromRequest(WP_REST_Request $request) : void
    {
        $orderId = (int) $request->get_param('orderId');

        $this->setShipmentTrackingService(ShipmentTrackingServiceFactory::getNewInstance()->getShipmentTrackingService($orderId));
    }

    /**
     * Gets the shipment with the specified ID and attaches the specified Shipping Rate to the first package.
     *
     * @param string $shipmentId
     * @param string $rateId
     * @param AccountContract $account
     * @return ShipmentContract
     * @throws ShippingException
     * @throws ShipmentNotFoundException
     * @throws ShippingExceptionContract
     */
    protected function getShipmentWithShippingRate(string $shipmentId, string $rateId, AccountContract $account) : ShipmentContract
    {
        if (! ($shipment = $this->getShipmentTrackingService()->getShipment($shipmentId))) {
            throw new ShippingException('Shipment not found.');
        }

        if (! $package = $this->getFirstShipmentPackage($shipment)) {
            throw new ShippingException('The shipment has no packages.');
        }

        /** @var ShippingRate $shippingRate */
        $shippingRate = $this->getShippingRateFromLabelService($rateId, $account);

        $package->setShippingRate($shippingRate);
        $shipment->setCarrier($shippingRate->getCarrier());

        return $shipment;
    }

    /**
     * Gets the first package of the given shipment or null if the shipment has no packages.
     *
     * @param string $rateId
     * @param AccountContract $account
     * @return ShippingRateContract
     * @throws ShippingExceptionContract
     */
    protected function getShippingRateFromLabelService(string $rateId, AccountContract $account) : ShippingRateContract
    {
        $operation = (new GetShippingRateOperation())
            ->setShippingRateId($rateId)
            ->setAccount($account);

        return $this->getShippingLabelsService()->getShippingRate($operation)->getShippingRate();
    }

    /**
     * Gets the first package of the given shipment or null if the shipment has no packages.
     *
     * @param ShipmentContract $shipment
     * @return PackageContract|null
     */
    protected function getFirstShipmentPackage(ShipmentContract $shipment) : ?PackageContract
    {
        $packages = array_values($shipment->getPackages());

        return array_shift($packages);
    }

    /**
     * Gets an operation object with information to purchase shipping labels.
     *
     * @param ShipmentContract $shipment
     * @param AccountContract $account
     * @return PurchaseShippingLabelsOperationContract
     */
    protected function getPurchaseShippingLabelsOperation(ShipmentContract $shipment, AccountContract $account) : PurchaseShippingLabelsOperationContract
    {
        return (new PurchaseShippingLabelsOperation())
            ->setAccount($account)
            ->setShipment($shipment)
            ->setFormat('pdf')
            ->setLayout('4x6');
    }

    /**
     * Converts the given {@see ShipmentContract} instance into an array of data.
     *
     * @param ShipmentContract $shipment
     * @return array<string, mixed>
     */
    protected function getShipmentData(ShipmentContract $shipment) : array
    {
        return ShipmentAdapter::getNewInstance([])->convertToSource($shipment);
    }

    /**
     * Returns the schema for REST items provided by the controller.
     *
     * @return array<string, mixed>
     */
    public function getItemSchema() : array
    {
        return [
            '$schema'    => 'http://json-schema.org/draft-04/schema#',
            'title'      => 'response',
            'type'       => 'object',
            'properties' => [
                'shipment' => [
                    'description' => __('The shipment object associated with the shipping label.', 'mwc-core'),
                    'type'        => 'object',
                    'properties'  => [
                        'id' => [
                            'description' => __('The shipment ID.', 'mwc-core'),
                            'type'        => 'string',
                            'context'     => ['view', 'edit'],
                            'readonly'    => true,
                        ],
                        'createdAt' => [
                            'description' => __("The shipment's creation date.", 'mwc-core'),
                            'type'        => 'string',
                            'context'     => ['view', 'edit'],
                            'readonly'    => true,
                        ],
                        'updatedAt' => [
                            'description' => __("The shipment's last updated date.", 'mwc-core'),
                            'type'        => 'string',
                            'context'     => ['view', 'edit'],
                            'readonly'    => true,
                        ],
                        'shippingProvider' => [
                            'description' => __('The shipping provider for the shipment.', 'mwc-core'),
                            'type'        => 'string',
                            'context'     => ['view', 'edit'],
                        ],
                        'otherShippingProviderDescription' => [
                            'description' => __('The label for a custom shipping provider.', 'mwc-core'),
                            'type'        => 'string',
                            'context'     => ['view', 'edit'],
                        ],
                        'trackingNumber' => [
                            'description' => __("The shipment's tracking number.", 'mwc-core'),
                            'type'        => 'string',
                            'context'     => ['view', 'edit'],
                        ],
                        'trackingUrl' => [
                            'description' => __("The shipment's tracking URL.", 'mwc-core'),
                            'type'        => 'string',
                            'context'     => ['view', 'edit'],
                        ],
                        'rate' => [
                            'description' => __('The rate associated with the shipment.', 'mwc-core'),
                            'type'        => 'object',
                            'context'     => ['view', 'edit'],
                        ],
                        'label' => [
                            'description' => __('The label associated with shipment.', 'mwc-core'),
                            'type'        => 'object',
                            'context'     => ['view', 'edit'],
                        ],
                        'items' => [
                            'description' => __('The items included in the shipment.', 'mwc-core'),
                            'type'        => 'array',
                            'items'       => [
                                'type'       => 'object',
                                'properties' => [
                                    'id' => [
                                        'description' => __("The item's ID.", 'mwc-core'),
                                        'type'        => 'integer',
                                        'context'     => ['view', 'edit'],
                                    ],
                                    'productId' => [
                                        'description' => __("The product's ID.", 'mwc-core'),
                                        'type'        => 'integer',
                                        'context'     => ['view', 'edit'],
                                    ],
                                    'variationId' => [
                                        'description' => __("The product's variation ID.", 'mwc-core'),
                                        'type'        => 'integer',
                                        'context'     => ['view', 'edit'],
                                    ],
                                    'quantity' => [
                                        'description' => __("The item's quantity.", 'mwc-core'),
                                        'type'        => 'number',
                                        'context'     => ['view', 'edit'],
                                    ],
                                ],
                            ],
                            'context' => ['view', 'edit'],
                        ],
                    ],
                    'context'  => ['view', 'edit'],
                    'readonly' => true,
                ],
                'label' => [
                    'description' => __('The shipping label returned by the shipping provider.', 'mwc-core'),
                    'type'        => 'array',
                    'items'       => [
                        'type' => 'object',
                    ],
                ],
            ],
        ];
    }
}
