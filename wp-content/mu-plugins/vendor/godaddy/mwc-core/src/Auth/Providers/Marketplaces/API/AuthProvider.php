<?php

namespace GoDaddy\WordPress\MWC\Core\Auth\Providers\Marketplaces\API;

use GoDaddy\WordPress\MWC\Common\Auth\AuthProviderFactory;
use GoDaddy\WordPress\MWC\Common\Auth\Contracts\AuthCredentialsContract;
use GoDaddy\WordPress\MWC\Common\Auth\Contracts\AuthMethodContract;
use GoDaddy\WordPress\MWC\Common\Auth\Contracts\AuthProviderContract;
use GoDaddy\WordPress\MWC\Common\Auth\Exceptions\AuthProviderException;
use GoDaddy\WordPress\MWC\Common\Auth\Exceptions\CredentialsCreateFailedException;
use GoDaddy\WordPress\MWC\Common\Auth\Methods\TokenAuthMethod;
use GoDaddy\WordPress\MWC\Common\Auth\Providers\Models\Token;

/**
 * Auth provider to prepare requests to the Marketplaces API by adding a platform site JWT fetched from the MWC API.
 */
class AuthProvider implements AuthProviderContract
{
    /**
     * Retrieves the credentials.
     *
     * @return AuthCredentialsContract
     * @throws AuthProviderException|CredentialsCreateFailedException
     */
    public function getCredentials() : AuthCredentialsContract
    {
        // reuse implementation from the Managed WooCommerce provider, as it is already customized to each platform
        return AuthProviderFactory::getNewInstance()->getManagedWooCommerceAuthProvider()->getCredentials();
    }

    /**
     * {@inheritDoc}
     */
    public function getMethod() : AuthMethodContract
    {
        /** @var $credentials Token */
        $credentials = $this->getCredentials();

        return (new TokenAuthMethod())
            ->setToken($credentials->getAccessToken())
            ->setType('sso-jwt');
    }

    /**
     * deleteCredentials will do nothing in this case, because the credentials are always available as constants, so we
     * can’t delete them and don’t need to store them in the cache either.
     */
    public function deleteCredentials() : void
    {
        // NOOP
    }
}
