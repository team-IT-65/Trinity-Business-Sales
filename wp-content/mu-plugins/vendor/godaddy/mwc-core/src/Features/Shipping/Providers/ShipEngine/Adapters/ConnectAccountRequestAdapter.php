<?php

namespace GoDaddy\WordPress\MWC\Core\Features\Shipping\Providers\ShipEngine\Adapters;

use GoDaddy\WordPress\MWC\Common\Configuration\Configuration;
use GoDaddy\WordPress\MWC\Common\Helpers\ArrayHelper;
use GoDaddy\WordPress\MWC\Common\Helpers\StringHelper;
use GoDaddy\WordPress\MWC\Common\Http\Contracts\RequestContract;
use GoDaddy\WordPress\MWC\Common\Http\Contracts\ResponseContract;
use GoDaddy\WordPress\MWC\Core\Features\Shipping\Providers\ShipEngine\Http\Request;
use GoDaddy\WordPress\MWC\Shipping\Adapters\AbstractGatewayRequestAdapter;
use GoDaddy\WordPress\MWC\Shipping\Exceptions\ShippingException;
use GoDaddy\WordPress\MWC\Shipping\Models\Account\Statuses\ConnectedStatus;
use GoDaddy\WordPress\MWC\Shipping\Models\Account\Statuses\DisconnectedStatus;
use GoDaddy\WordPress\MWC\Shipping\Models\Contracts\AccountContract;
use GoDaddy\WordPress\MWC\Shipping\Models\Contracts\AccountStatusContract;

class ConnectAccountRequestAdapter extends AbstractGatewayRequestAdapter
{
    /** @var AccountContract */
    protected $account;

    public function __construct(AccountContract $account)
    {
        $this->account = $account;
    }

    /** {@inheritdoc} */
    public function convertFromSource() : RequestContract
    {
        return Request::withAuth()
            ->setPath('/shipping/onboarding/account')
            ->setMethod('post')
            ->setBody(ArrayHelper::whereNotNull([
                'externalAccountId' => $this->account->getId(),
                'firstName'         => $this->account->getFirstName() ?: null,
                'lastName'          => $this->account->getLastName() ?: null,
                'companyName'       => $this->getCompanyName(),
                'originCountryCode' => $this->account->getOriginCountryCode(),
            ]));
    }

    /**
     * Gets a company name that is at most 50 characters long.
     *
     * @return string|null
     */
    protected function getCompanyName() : ?string
    {
        if (! $companyName = $this->account->getCompanyName()) {
            return null;
        }

        return StringHelper::substring($companyName, 0, (int) Configuration::get('shipping.shipengine.account.maxCompanyNameLength'));
    }

    /** {@inheritdoc} */
    protected function convertResponse(ResponseContract $response)
    {
        $this->account->setRemoteId($this->getAccountRemoteId($response));
        $this->account->setStatus($this->getAccountStatus($response));

        return $this->account;
    }

    /**
     * Gets the remote ID for the account from the given response.
     *
     * @param ResponseContract $response
     * @return string
     * @throws ShippingException
     */
    protected function getAccountRemoteId(ResponseContract $response) : string
    {
        if (! $remoteId = ArrayHelper::get(ArrayHelper::wrap($response->getBody()), 'id')) {
            throw new ShippingException('The response does not include the remote ID of the shipping account.');
        }

        return $remoteId;
    }

    /**
     * Gets the account status from the given response.
     *
     * @param ResponseContract $response
     * @return AccountStatusContract
     * @throws ShippingException
     */
    protected function getAccountStatus(ResponseContract $response) : AccountStatusContract
    {
        $active = ArrayHelper::get(ArrayHelper::wrap($response->getBody()), 'active');

        if (is_null($active)) {
            throw new ShippingException('The response does not include the status of the shipping account.');
        }

        return $active ? new ConnectedStatus() : new DisconnectedStatus();
    }
}
