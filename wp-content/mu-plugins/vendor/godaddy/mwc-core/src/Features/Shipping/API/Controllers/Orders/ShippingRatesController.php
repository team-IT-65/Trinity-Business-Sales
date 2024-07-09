<?php

namespace GoDaddy\WordPress\MWC\Core\Features\Shipping\API\Controllers\Orders;

use Exception;
use GoDaddy\WordPress\MWC\Common\API\Response;
use GoDaddy\WordPress\MWC\Common\Exceptions\BaseException;
use GoDaddy\WordPress\MWC\Common\Helpers\ArrayHelper;
use GoDaddy\WordPress\MWC\Core\Features\Shipping\API\Controllers\AbstractController;
use GoDaddy\WordPress\MWC\Core\Features\Shipping\API\Traits\CanGetShippingAccountServiceTrait;
use GoDaddy\WordPress\MWC\Core\Features\Shipping\Services\Contracts\ShippingLabelsServiceContract;
use GoDaddy\WordPress\MWC\Core\Features\Shipping\Services\ShippingLabelsService;
use GoDaddy\WordPress\MWC\Dashboard\Exceptions\OrderNotFoundException;
use GoDaddy\WordPress\MWC\Dashboard\Exceptions\ShipmentValidationFailedException;
use GoDaddy\WordPress\MWC\Dashboard\Shipping\DataSources\Request\Adapters\ShipmentAdapter;
use GoDaddy\WordPress\MWC\Dashboard\Shipping\DataSources\Request\Adapters\ShippingRateAdapter;
use GoDaddy\WordPress\MWC\Dashboard\Shipping\Fulfillment;
use GoDaddy\WordPress\MWC\Shipping\Contracts\CalculateShippingRatesOperationContract;
use GoDaddy\WordPress\MWC\Shipping\Contracts\ShipmentContract;
use GoDaddy\WordPress\MWC\Shipping\Exceptions\Contracts\ShippingExceptionContract;
use GoDaddy\WordPress\MWC\Shipping\Exceptions\ShippingException;
use GoDaddy\WordPress\MWC\Shipping\Models\Contracts\AccountContract;
use GoDaddy\WordPress\MWC\Shipping\Models\Contracts\CarrierContract;
use GoDaddy\WordPress\MWC\Shipping\Models\Contracts\ShippingRateContract;
use GoDaddy\WordPress\MWC\Shipping\Operations\CalculateShippingRatesOperation;
use GoDaddy\WordPress\MWC\Shipping\Operations\ListCarriersOperation;
use WP_REST_Request;
use WP_REST_Response;

class ShippingRatesController extends AbstractController
{
    use CanGetShippingAccountServiceTrait;

    /** @var string */
    protected $route = 'orders/(?P<orderId>[0-9]+)/rates';

    /** @var ?ShippingLabelsServiceContract */
    protected $shippingLabelsService;

    /** {@inheritDoc} */
    public function registerRoutes() : void
    {
        register_rest_route($this->namespace, "/{$this->route}", [
            [
                'methods'             => 'POST',
                'callback'            => [$this, 'calculateRates'],
                'permission_callback' => [$this, 'createItemPermissionsCheck'],
            ],
            'args' => [
                'orderId' => [
                    'required'          => true,
                    'type'              => 'integer',
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
    protected function setShippingLabelsService(ShippingLabelsServiceContract $value) : ShippingRatesController
    {
        $this->shippingLabelsService = $value;

        return $this;
    }

    /**
     * Handles the POST orders/{orderId}/rates endpoint.
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function calculateRates(WP_REST_Request $request) : WP_REST_Response
    {
        try {
            return $this->processCalculateRatesRequest($request);
        } catch (ShippingExceptionContract $exception) {
            return $this->getShippingExceptionErrorResponse($exception);
        } catch (Exception $exception) {
            return $this->getGenericExceptionErrorResponse($exception);
        }
    }

    /**
     * Processes the request to calculate shipping rates.
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     * @throws OrderNotFoundException
     * @throws ShippingExceptionContract
     * @throws ShipmentValidationFailedException
     * @throws BaseException
     */
    protected function processCalculateRatesRequest(WP_REST_Request $request) : WP_REST_Response
    {
        $this->setShippingLabelsServiceFromRequest($request);

        $operation = $this->getCalculateShippingRatesOperation($request->get_json_params());

        /** @throws BaseException from {@see Fulfillment::findShipment()} */
        $operation = $this->getShippingLabelsService()->calculateShippingRates($operation);

        return $this->getWordPressResponse(
            (new Response())
                ->setBody([
                    'shipment' => $this->getShipmentData($operation->getShipment()),
                    'rates'    => $this->getShippingRatesData($operation->getShippingRates()),
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
     * Gets an operation object with information to calculate shipping rates.
     *
     * @param array<string, mixed> $payload
     * @return CalculateShippingRatesOperationContract
     * @throws ShippingExceptionContract
     */
    protected function getCalculateShippingRatesOperation(array $payload) : CalculateShippingRatesOperationContract
    {
        $shipment = $this->getShipmentFromRequest($payload);
        $account = $this->getShippingAccountService()->getAccount();

        return (new CalculateShippingRatesOperation())
            ->setAccount($account)
            ->setShipment($shipment)
            ->setCarriers(...$this->getCarriers($account));
    }

    /**
     * Converts that given request data into a {@see ShipmentContract} instance.
     *
     * @param array<string, mixed> $payload
     * @return ShipmentContract
     * @throws ShippingExceptionContract
     */
    protected function getShipmentFromRequest(array $payload) : ShipmentContract
    {
        if (! ArrayHelper::get($payload, 'originAddress')) {
            throw new ShippingException('The origin address is required.');
        }

        if (! ArrayHelper::get($payload, 'destinationAddress')) {
            throw new ShippingException('The destination address is required.');
        }

        if (! ArrayHelper::get($payload, 'items')) {
            throw new ShippingException('The list of items is required.');
        }

        if (! ArrayHelper::get($payload, 'weight')) {
            throw new ShippingException('The weight is required.');
        }

        return ShipmentAdapter::getNewInstance($payload)->convertFromSource();
    }

    /**
     * Gets a list of carriers available for the given account.
     *
     * @param AccountContract $account
     * @return CarrierContract[]
     * @throws ShippingExceptionContract
     */
    protected function getCarriers(AccountContract $account) : array
    {
        $operation = (new ListCarriersOperation())->setAccount($account);

        $operation = $this->getShippingLabelsService()->getCarriers($operation);

        return $operation->getCarriers();
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
     * Converts the given array of {@see ShippingRateContract} instances into an array of data.
     *
     * @param ShippingRateContract[] $shippingRates
     * @return array<string, mixed>[]
     */
    protected function getShippingRatesData(array $shippingRates) : array
    {
        return ArrayHelper::whereNotNull(array_map(
            static function (ShippingRateContract $shippingRate) {
                return ShippingRateAdapter::getNewInstance([])->convertToSource($shippingRate);
            },
            $shippingRates
        ));
    }

    /**
     * Returns the schema for REST items provided by the controller.
     *
     * TODO: how can we share schemas for models that appear in the response of various endpoints? -- https://jira.godaddy.com/browse/MWC-7410 {wvega 2022-08-04}
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
                    'description' => __('The shipment object associated with the shipping rates.', 'mwc-core'),
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
                            'description' => _x('The label for a custom shipping provider.', 'Display title, not physical label', 'mwc-core'),
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
                'rates' => [
                    'description' => __('The shipping rates returned by the shipping provider.', 'mwc-core'),
                    'type'        => 'array',
                    'items'       => [
                        'type' => 'object',
                    ],
                ],
            ],
        ];
    }
}
