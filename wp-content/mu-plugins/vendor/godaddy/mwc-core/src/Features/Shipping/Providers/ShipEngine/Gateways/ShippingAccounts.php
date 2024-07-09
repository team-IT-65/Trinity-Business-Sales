<?php

namespace GoDaddy\WordPress\MWC\Core\Features\Shipping\Providers\ShipEngine\Gateways;

use GoDaddy\WordPress\MWC\Core\Features\Shipping\Contracts\ShippingAccountsGatewayContract;
use GoDaddy\WordPress\MWC\Core\Features\Shipping\Providers\ShipEngine\Adapters\ConnectAccountRequestAdapter;
use GoDaddy\WordPress\MWC\Core\Features\Shipping\Providers\ShipEngine\Adapters\GetDashboardUrlRequestAdapter;
use GoDaddy\WordPress\MWC\Shipping\Gateways\AbstractGateway;
use GoDaddy\WordPress\MWC\Shipping\Models\Account\Statuses\DisconnectedStatus;
use GoDaddy\WordPress\MWC\Shipping\Models\Contracts\AccountContract;
use GoDaddy\WordPress\MWC\Shipping\Traits\CanConnectShippingAccountTrait;
use GoDaddy\WordPress\MWC\Shipping\Traits\CanGetDashboardUrlTrait;

class ShippingAccounts extends AbstractGateway implements ShippingAccountsGatewayContract
{
    use CanConnectShippingAccountTrait;
    use CanGetDashboardUrlTrait;

    public function __construct()
    {
        $this->connectAccountRequestAdapter = ConnectAccountRequestAdapter::class;
        $this->getDashboardUrlRequestAdapter = GetDashboardUrlRequestAdapter::class;
    }

    /**
     * Disconnects from given shipping account.
     *
     * @param AccountContract $account
     * @return AccountContract
     */
    public function disconnect(AccountContract $account) : AccountContract
    {
        return $account->setStatus(new DisconnectedStatus());
    }
}
