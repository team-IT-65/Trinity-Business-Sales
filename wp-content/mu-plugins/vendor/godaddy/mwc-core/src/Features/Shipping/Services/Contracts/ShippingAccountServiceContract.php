<?php

namespace GoDaddy\WordPress\MWC\Core\Features\Shipping\Services\Contracts;

use GoDaddy\WordPress\MWC\Shipping\Contracts\GetDashboardUrlOperationContract;
use GoDaddy\WordPress\MWC\Shipping\Exceptions\Contracts\ShippingExceptionContract;
use GoDaddy\WordPress\MWC\Shipping\Models\Contracts\AccountContract;

interface ShippingAccountServiceContract
{
    /**
     * Gets the shipping account for this site.
     *
     * @return AccountContract
     */
    public function getAccount() : AccountContract;

    /**
     * Attempts to connect the given account.
     *
     * @param AccountContract $account
     * @return AccountContract
     * @throws ShippingExceptionContract
     */
    public function connectAccount(AccountContract $account) : AccountContract;

    /**
     * Attempts to disconnect the given account.
     *
     * @param AccountContract $account
     * @return AccountContract
     * @throws ShippingExceptionContract
     */
    public function disconnectAccount(AccountContract $account) : AccountContract;

    /**
     * Gets the dashboard URL for the given account.
     *
     * @param GetDashboardUrlOperationContract $operation
     * @return GetDashboardUrlOperationContract
     * @throws ShippingExceptionContract
     */
    public function getDashboardUrl(GetDashboardUrlOperationContract $operation) : GetDashboardUrlOperationContract;
}
