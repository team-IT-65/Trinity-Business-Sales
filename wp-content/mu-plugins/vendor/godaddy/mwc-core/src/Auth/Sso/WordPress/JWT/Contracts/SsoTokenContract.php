<?php

namespace GoDaddy\WordPress\MWC\Core\Auth\Sso\WordPress\JWT\Contracts;

use GoDaddy\WordPress\MWC\Common\Auth\JWT\Contracts\TokenContract;

/**
 * Contract for object representations of a token that can be used for SSO.
 */
interface SsoTokenContract extends TokenContract
{
    /**
     * Gets the WordPress username to login as.
     *
     * @return string
     */
    public function getUsername() : string;

    /**
     * Gets the customer ID. This is always the customer ID associated with the current site.
     *
     * @return string
     */
    public function getCustomerId() : string;

    /**
     * Gets the authentication type. Expected to be one of: `basic`, `s2s`, `e2s`.
     *
     * @return string
     */
    public function getAuthType() : string;

    /**
     * Gets the delegate's (impersonator's) customer ID. This is tied to the individual user who's attempting to sign on.
     *
     * @return string|null
     */
    public function getDelegateCustomerId() : ?string;

    /**
     * Get the JWT ID.
     *
     * @return string
     */
    public function getJti() : string;
}
