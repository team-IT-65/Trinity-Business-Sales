<?php

namespace GoDaddy\WordPress\MWC\Core\Auth\API\Controllers;

use function __;
use function current_user_can;
use Exception;
use GoDaddy\WordPress\MWC\Common\Auth\AuthProviderFactory;
use GoDaddy\WordPress\MWC\Common\Components\Contracts\ComponentContract;
use GoDaddy\WordPress\MWC\Dashboard\API\Controllers\AbstractController;
use function register_rest_route;
use function rest_ensure_response;
use WP_Error;
use WP_REST_Response;

class TokenController extends AbstractController implements ComponentContract
{
    /** @var string */
    protected $route = 'token';

    /**
     * Initializes the controller.
     */
    public function load()
    {
        $this->registerRoutes();
    }

    /**
     * Registers the API routes for the endpoints provided by the controller.
     */
    public function registerRoutes()
    {
        register_rest_route($this->namespace, "/{$this->route}", [
            [
                'methods'             => 'POST',
                'callback'            => [$this, 'createItem'],
                'permission_callback' => [$this, 'createItemPermissionsCheck'],
            ],
        ]);
    }

    /**
     * Creates the JWT Authorization.
     *
     * @return WP_REST_Response|WP_Error
     */
    public function createItem()
    {
        try {
            $response = AuthProviderFactory::getNewInstance()->getEventsAuthProvider()->getCredentials()->toArray();
        } catch (Exception $exception) {
            $response = new WP_Error($exception->getCode() ?: 400, $exception->getMessage(), [
                'status' => $exception->getCode() ?: 400,
            ]);
        }

        return rest_ensure_response($response);
    }

    /**
     * Determines if the current user has permissions to issue requests to create items.
     *
     * @return bool
     */
    public function createItemPermissionsCheck() : bool
    {
        return current_user_can('manage_woocommerce');
    }

    /**
     * Gets the item schema.
     *
     * @return array
     */
    public function getItemSchema() : array
    {
        return [
            '$schema'    => 'http://json-schema.org/draft-04/schema#',
            'title'      => 'setting',
            'type'       => 'object',
            'properties' => [
                'accessToken' => [
                    'description' => __('The access token string as issued by the authorization server.', 'mwc-core'),
                    'type'        => 'string',
                    'context'     => ['view'],
                    'readonly'    => true,
                ],
                'expiresIn' => [
                    'description' => __('Number of seconds to expiration of the access token.', 'mwc-core'),
                    'type'        => 'integer',
                    'context'     => ['view'],
                    'readonly'    => true,
                ],
                'scope' => [
                    'description' => __('The scope the token granted.', 'mwc-core'),
                    'type'        => 'string',
                    'context'     => ['view'],
                    'readonly'    => true,
                ],
                'tokenId' => [
                    'description' => __('Access Token ID.', 'mwc-core'),
                    'type'        => 'string',
                    'context'     => ['view'],
                    'readonly'    => true,
                ],
                'tokenType' => [
                    'description' => __('The type of token this is.', 'mwc-core'),
                    'type'        => 'string',
                    'context'     => ['view'],
                    'readonly'    => true,
                ],
            ],
        ];
    }
}
