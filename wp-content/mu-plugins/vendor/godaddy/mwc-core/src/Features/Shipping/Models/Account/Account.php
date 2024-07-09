<?php

namespace GoDaddy\WordPress\MWC\Core\Features\Shipping\Models\Account;

use GoDaddy\WordPress\MWC\Common\Events\Events;
use GoDaddy\WordPress\MWC\Core\Features\Shipping\DataStores\AccountDataStore;
use GoDaddy\WordPress\MWC\Shipping\Models\Account\Account as ShippingAccount;

class Account extends ShippingAccount
{
    /**
     * Gets an instance of the given model class, if found.
     *
     * @param string|null $identifier
     * @return self
     */
    public static function get($identifier = null)
    {
        return AccountDataStore::getNewInstance()->read($identifier);
    }

    /**
     * Updates a given instance of the model class and saves it.
     *
     * @return $this
     */
    public function update()
    {
        return $this->save();
    }

    /**
     * Saves the instance of the class with its current state.
     *
     * @return $this
     */
    public function save()
    {
        $action = $this->getCreatedAt() ? 'update' : 'create';

        AccountDataStore::getNewInstance()->save($this);

        Events::broadcast($this->buildEvent('shipping_account', $action));

        return $this;
    }

    /**
     * Deletes a given instance of the model class.
     */
    public function delete() : void
    {
        AccountDataStore::getNewInstance()->delete($this);
    }
}
