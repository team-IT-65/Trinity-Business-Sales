<?php

namespace GoDaddy\WordPress\MWC\Core\Features\Shipping\Services;

use GoDaddy\WordPress\MWC\Common\Models\User;
use GoDaddy\WordPress\MWC\Common\Platforms\Exceptions\PlatformRepositoryException;
use GoDaddy\WordPress\MWC\Common\Platforms\PlatformRepositoryFactory;
use GoDaddy\WordPress\MWC\Common\Repositories\WooCommerceRepository;
use GoDaddy\WordPress\MWC\Common\Repositories\WordPress\SiteRepository;
use GoDaddy\WordPress\MWC\Common\Traits\CanGetNewInstanceTrait;
use GoDaddy\WordPress\MWC\Core\Features\Shipping\Contracts\ShippingProviderContract;
use GoDaddy\WordPress\MWC\Core\Features\Shipping\Models\Account\Account;
use GoDaddy\WordPress\MWC\Core\Features\Shipping\Services\Contracts\ShippingAccountServiceContract;
use GoDaddy\WordPress\MWC\Core\Features\Shipping\Services\Traits\HasShippingProviderTrait;
use GoDaddy\WordPress\MWC\Shipping\Contracts\GetDashboardUrlOperationContract;
use GoDaddy\WordPress\MWC\Shipping\Exceptions\ShippingException;
use GoDaddy\WordPress\MWC\Shipping\Models\Contracts\AccountContract;

class ShippingAccountService implements ShippingAccountServiceContract
{
    use CanGetNewInstanceTrait;
    use HasShippingProviderTrait;

    final public function __construct(ShippingProviderContract $shippingProvider = null)
    {
        $this->shippingProvider = $shippingProvider ?? static::getShippingProviderInstance();
    }

    /** {@inheritDoc} */
    public function getAccount() : AccountContract
    {
        return Account::get();
    }

    /**
     * {@inheritDoc}
     */
    public function connectAccount(AccountContract $account) : AccountContract
    {
        try {
            $account = $this->shippingProvider->accounts()->connect($this->prepareAccount($account));
        } catch (PlatformRepositoryException $e) {
            throw new ShippingException('Failed to prepare account', $e);
        }
        $account->save();

        return $account;
    }

    /**
     * Prepares account data.
     *
     * @param AccountContract $account
     * @return AccountContract
     * @throws PlatformRepositoryException
     */
    protected function prepareAccount(AccountContract $account) : AccountContract
    {
        $account->setId(PlatformRepositoryFactory::getNewInstance()->getPlatformRepository()->getChannelId());

        $user = User::getCurrent();
        $account->setFirstName($user ? (string) $user->getFirstName() : '');
        $account->setLastName($user ? (string) $user->getLastName() : '');

        $account->setCompanyName(SiteRepository::getTitle());
        $account->setOriginCountryCode(WooCommerceRepository::getBaseCountry());

        return $account;
    }

    /** {@inheritDoc} */
    public function disconnectAccount(AccountContract $account) : AccountContract
    {
        $account = $this->shippingProvider->accounts()->disconnect($account);
        $account->save();

        return $account;
    }

    /** {@inheritDoc} */
    public function getDashboardUrl(GetDashboardUrlOperationContract $operation) : GetDashboardUrlOperationContract
    {
        return $this->shippingProvider->accounts()->getDashboardUrl($operation);
    }
}
