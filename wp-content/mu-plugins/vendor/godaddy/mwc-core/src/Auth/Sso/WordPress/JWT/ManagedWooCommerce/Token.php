<?php

namespace GoDaddy\WordPress\MWC\Core\Auth\Sso\WordPress\JWT\ManagedWooCommerce;

use GoDaddy\WordPress\MWC\Common\Auth\JWT\Token as CommonToken;
use GoDaddy\WordPress\MWC\Common\Helpers\TypeHelper;
use GoDaddy\WordPress\MWC\Core\Auth\Sso\WordPress\JWT\Contracts\SsoTokenContract;

/**
 * SSO JWT token object provided by the MWC API.
 */
class Token extends CommonToken implements SsoTokenContract
{
    /**
     * Gets the WordPress username.
     *
     * @return string
     */
    public function getUsername() : string
    {
        return (string) $this->getData('sub', '');
    }

    /**
     * Gets the customer ID. This is always the customer ID associated with the current site.
     *
     * @return string
     */
    public function getCustomerId() : string
    {
        return (string) $this->getData('godaddy:customerId', '');
    }

    /**
     * Gets the authentication type. Expected to be one of: `basic`, `s2s`, `e2s`.
     *
     * @return string
     */
    public function getAuthType() : string
    {
        return TypeHelper::string($this->getData('godaddy:authType'), '');
    }

    /**
     * Gets the delegate's (impersonator's) customer ID. This is tied to the individual user who's attempting to sign on.
     *
     * @return string|null
     */
    public function getDelegateCustomerId() : ?string
    {
        return TypeHelper::stringOrNull($this->getData('godaddy:delegateCustomerId')) ?: null;
    }

    /**
     * {@inheritDoc}
     */
    public function getJti() : string
    {
        /** @var string|int $jti */
        $jti = $this->getData('jti', '');

        return (string) $jti;
    }
}
