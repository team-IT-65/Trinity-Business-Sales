<?php

namespace GoDaddy\WordPress\MWC\Core\Auth\Sso\WordPress\JWT\Validation;

use GoDaddy\WordPress\MWC\Common\Validation\Contracts\ValidationRuleContract;
use GoDaddy\WordPress\MWC\Core\Auth\Sso\WordPress\JWT\Contracts\SsoTokenContract;

/**
 * Checks if the JWT contains a username.
 */
class TokenContainsUsernameRule implements ValidationRuleContract
{
    /**
     * {@inheritDoc}
     */
    public function passes($input) : bool
    {
        if ($input instanceof SsoTokenContract) {
            return ! empty($input->getUsername());
        }

        return ! empty($input['username']);
    }
}
