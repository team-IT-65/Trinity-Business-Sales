<?php

namespace GoDaddy\WordPress\MWC\Core\Features\Shipping\API\Controllers;

use Exception;
use GoDaddy\WordPress\MWC\Common\API\Response;
use GoDaddy\WordPress\MWC\Common\Components\Contracts\ComponentContract;
use GoDaddy\WordPress\MWC\Dashboard\API\Controllers\AbstractController as DashboardAbstractController;
use GoDaddy\WordPress\MWC\Shipping\Exceptions\Contracts\ShippingExceptionContract;
use GoDaddy\WordPress\MWC\Shipping\Exceptions\ShippingException;
use WP_REST_Response;

abstract class AbstractController extends DashboardAbstractController implements ComponentContract
{
    /** {@inheritdoc} */
    public function load() : void
    {
        $this->registerRoutes();
    }

    /**
     * Gets a WordPress response with error information for the given exception.
     *
     * @param ShippingExceptionContract $exception
     * @return WP_REST_Response
     */
    protected function getShippingExceptionErrorResponse(ShippingExceptionContract $exception) : WP_REST_Response
    {
        $errorCode = $exception instanceof ShippingException ? $exception->getErrorCode() : null;

        return $this->getErrorResponse($exception->getCode(), $exception->getMessage(), $errorCode);
    }

    /**
     * Gets a WordPress response with error information for the given exception.
     *
     * @param Exception $exception
     * @return WP_REST_Response
     */
    protected function getGenericExceptionErrorResponse(Exception $exception) : WP_REST_Response
    {
        return $this->getErrorResponse($exception->getCode() ?: 500, $exception->getMessage());
    }

    /**
     * Gets a WordPress response with the given error information.
     *
     * @param int $statusCode
     * @param string $errorMessage
     * @param string|null $errorCode
     * @return WP_REST_Response
     */
    protected function getErrorResponse(int $statusCode, string $errorMessage, ?string $errorCode = null) : WP_REST_Response
    {
        $response = Response::getNewInstance()
            ->setStatus($statusCode)
            ->addError($errorMessage, $errorCode);

        return $this->getWordPressResponse($response);
    }
}
