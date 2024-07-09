<?php

namespace GoDaddy\WordPress\MWC\Core\Features\Shipping\API\Controllers;

use GoDaddy\WordPress\MWC\Common\API\Response;
use GoDaddy\WordPress\MWC\Common\Helpers\ArrayHelper;
use GoDaddy\WordPress\MWC\Common\Helpers\StringHelper;
use GoDaddy\WordPress\MWC\Core\Features\Shipping\API\Traits\CanGetShippingAccountServiceTrait;
use GoDaddy\WordPress\MWC\Shipping\Exceptions\Contracts\ShippingExceptionContract;
use GoDaddy\WordPress\MWC\Shipping\Models\Contracts\AccountContract;
use WP_REST_Request;
use WP_REST_Response;

class AccountController extends AbstractController
{
    use CanGetShippingAccountServiceTrait;

    /** @var string */
    protected $route = 'shipping/account';

    /**
     * Handler for the GET request for this endpoint.
     *
     * @return WP_REST_Response
     */
    public function getAccount() : WP_REST_Response
    {
        return $this->getAccountResponse($this->getShippingAccountService()->getAccount());
    }

    /**
     * Gets a WordPress response with data for the given account.
     *
     * @param AccountContract $account
     * @return WP_REST_Response
     */
    protected function getAccountResponse(AccountContract $account) : WP_REST_Response
    {
        $response = Response::getNewInstance()->setBody($this->getAccountData($account));

        return $this->getWordPressResponse($response);
    }

    /**
     * Gets the response data for the given {@see AccountContract} instance.
     *
     * @param AccountContract $account
     * @return array{companyName: string, originCountryCode: string, status: string}
     */
    protected function getAccountData(AccountContract $account) : array
    {
        return [
            'companyName'       => $account->getCompanyName(),
            'originCountryCode' => $account->getOriginCountryCode(),
            'status'            => $account->getStatus()->getName(),
        ];
    }

    /**
     * Handler for the PUT request for this endpoint.
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function updateAccount(WP_REST_Request $request) : WP_REST_Response
    {
        $payload = $request->get_json_params();

        try {
            if ($this->shouldUpdateAccountStatus($payload)) {
                return $this->maybeUpdateAccountStatus($payload);
            }
        } catch (ShippingExceptionContract $exception) {
            return $this->getShippingExceptionErrorResponse($exception);
        }

        return $this->getAccount();
    }

    /**
     * Determines whether the request includes payload to update the account status.
     *
     * @param array<string, mixed> $payload
     * @return bool
     */
    protected function shouldUpdateAccountStatus(array $payload) : bool
    {
        return (bool) $this->getSubmittedAccountStatus($payload);
    }

    /**
     * Gets the submitted account status.
     *
     * @param array<string, mixed> $payload
     * @return string
     */
    protected function getSubmittedAccountStatus(array $payload) : string
    {
        return (string) StringHelper::ensureScalar(ArrayHelper::get($payload, 'status'));
    }

    /**
     * Attempts to connect or disconnect the account based on the submitted account status.
     *
     * @param array<string, mixed> $payload
     * @throws ShippingExceptionContract
     * @return WP_REST_Response
     */
    protected function maybeUpdateAccountStatus(array $payload) : WP_REST_Response
    {
        $account = $this->getShippingAccountService()->getAccount();
        $status = $this->getSubmittedAccountStatus($payload);

        if ($status === 'connected') {
            $this->getShippingAccountService()->connectAccount($account);
        } elseif ($status === 'disconnected') {
            $this->getShippingAccountService()->disconnectAccount($account);
        }

        return $this->getAccountResponse($account);
    }

    /** {@inheritDoc} */
    public function registerRoutes() : void
    {
        register_rest_route($this->namespace, "/{$this->route}", [
            [
                'methods'             => 'GET',
                'callback'            => [$this, 'getAccount'],
                'permission_callback' => [$this, 'getItemsPermissionsCheck'],
            ],
        ]);

        register_rest_route($this->namespace, "/{$this->route}", [
            [
                'methods'             => 'PUT',
                'callback'            => [$this, 'updateAccount'],
                'permission_callback' => [$this, 'updateItemPermissionsCheck'],
            ],
        ]);
    }

    /**
     * Determines if the current user has permissions to issue requests to get items.
     *
     * @return bool
     */
    public function getItemsPermissionsCheck() : bool
    {
        return current_user_can('manage_woocommerce');
    }

    /**
     * Determines if the current user has permissions to issue requests to update items.
     *
     * @return bool
     */
    public function updateItemPermissionsCheck() : bool
    {
        return current_user_can('manage_woocommerce');
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
            'title'      => 'setting',
            'type'       => 'object',
            'properties' => [
                'companyName' => [
                    'description' => __('Company name.', 'mwc-core'),
                    'type'        => 'string',
                    'context'     => ['view', 'edit'],
                    'readonly'    => true,
                ],
                'originCountryCode' => [
                    'description' => __('Country code.', 'mwc-core'),
                    'type'        => 'string',
                    'context'     => ['view', 'edit'],
                    'readonly'    => true,
                ],
                'status' => [
                    'description' => __('Connection status.', 'mwc-core'),
                    'type'        => 'string',
                    'context'     => ['view', 'edit'],
                    'readonly'    => true,
                    'enum'        => [
                        'not-configured',
                        'connected',
                        'disconnected',
                    ],
                ],
            ],
        ];
    }
}
