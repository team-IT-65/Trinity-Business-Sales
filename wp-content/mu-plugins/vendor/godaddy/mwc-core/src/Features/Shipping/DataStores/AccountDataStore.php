<?php

namespace GoDaddy\WordPress\MWC\Core\Features\Shipping\DataStores;

use DateTime;
use Exception;
use GoDaddy\WordPress\MWC\Common\Helpers\ArrayHelper;
use GoDaddy\WordPress\MWC\Common\Repositories\WooCommerceRepository;
use GoDaddy\WordPress\MWC\Common\Repositories\WordPress\SiteRepository;
use GoDaddy\WordPress\MWC\Common\Traits\CanGetNewInstanceTrait;
use GoDaddy\WordPress\MWC\Core\Features\Shipping\Contracts\AccountDataStoreContract;
use GoDaddy\WordPress\MWC\Core\Features\Shipping\Models\Account\Account;
use GoDaddy\WordPress\MWC\Shipping\Models\Account\Statuses\ConnectedStatus;
use GoDaddy\WordPress\MWC\Shipping\Models\Account\Statuses\DisconnectedStatus;
use GoDaddy\WordPress\MWC\Shipping\Models\Account\Statuses\NotConfiguredStatus;
use GoDaddy\WordPress\MWC\Shipping\Models\Contracts\AccountContract;
use GoDaddy\WordPress\MWC\Shipping\Models\Contracts\AccountStatusContract;

class AccountDataStore implements AccountDataStoreContract
{
    use CanGetNewInstanceTrait;

    protected const SHIPPING_ACCOUNT_OPTION_NAME = 'mwc_shipping_account';

    /**
     * Reads an account from the data store.
     *
     * @param string|null $identifier
     * @return Account
     */
    public function read(?string $identifier = null) : AccountContract
    {
        return Account::seed($this->getAccountDataFromDatabase());
    }

    /**
     * Gets the account data from the database.
     *
     * @return array<string, string>
     */
    protected function getAccountDataFromDatabase() : array
    {
        $stored = ArrayHelper::wrap(get_option(static::SHIPPING_ACCOUNT_OPTION_NAME));

        return ArrayHelper::whereNotNull([
            'id'                => ArrayHelper::getStringValueForKey($stored, 'id'),
            'remoteId'          => ArrayHelper::getStringValueForKey($stored, 'remoteId') ?: null,
            'firstName'         => ArrayHelper::getStringValueForKey($stored, 'firstName'),
            'lastName'          => ArrayHelper::getStringValueForKey($stored, 'lastName'),
            'companyName'       => ArrayHelper::getStringValueForKey($stored, 'companyName') ?: SiteRepository::getTitle(),
            'originCountryCode' => ArrayHelper::getStringValueForKey($stored, 'originCountryCode') ?: WooCommerceRepository::getBaseCountry(),
            'status'            => $this->getAccountStatus(ArrayHelper::getStringValueForKey($stored, 'status')),
            'createdAt'         => $this->getDateTimeValue($stored, 'createdAt'),
            'updatedAt'         => $this->getDateTimeValue($stored, 'updatedAt'),
        ]);
    }

    /**
     * Gets an instance of {@see AccountStatusContract} for the given status name.
     *
     * @param string $status
     * @return AccountStatusContract
     */
    protected function getAccountStatus(string $status) : AccountStatusContract
    {
        switch ($status) {
            case 'disconnected':
                return new DisconnectedStatus();
            case 'connected':
                return new ConnectedStatus();
            default:
                return new NotConfiguredStatus();
        }
    }

    /**
     * Gets a {@see DateTime} object from a timestamp in the given array.
     *
     * @param array<string, mixed> $stored
     * @param string $key
     * @return DateTime|null
     */
    protected function getDateTimeValue(array $stored, string $key) : ?DateTime
    {
        $timestamp = ArrayHelper::get($stored, $key);

        if (! is_numeric($timestamp)) {
            return null;
        }

        try {
            return new DateTime("@{$timestamp}");
        } catch (Exception $exception) {
            return null;
        }
    }

    /** {@inheritdoc} */
    public function save(AccountContract $account) : AccountContract
    {
        if (! $account->getCreatedAt()) {
            $account->setCreatedAt(new DateTime());
        }

        $account->setUpdatedAt(new DateTime());

        update_option(static::SHIPPING_ACCOUNT_OPTION_NAME, $this->getAccountDataForDatabase($account));

        return $account;
    }

    /**
     * Gets the account data to be stored in the database.
     *
     * @return array<string, string>
     */
    protected function getAccountDataForDatabase(AccountContract $account) : array
    {
        $data = $account->toArray();

        $data['status'] = $account->getStatus()->getName();
        $data['createdAt'] = $account->getCreatedAt() ? $account->getCreatedAt()->getTimestamp() : null;
        $data['updatedAt'] = $account->getUpdatedAt() ? $account->getUpdatedAt()->getTimestamp() : null;

        return ArrayHelper::whereNotNull($data);
    }

    /** {@inheritdoc} */
    public function delete(AccountContract $account) : AccountContract
    {
        delete_option(static::SHIPPING_ACCOUNT_OPTION_NAME);

        return $account;
    }
}
