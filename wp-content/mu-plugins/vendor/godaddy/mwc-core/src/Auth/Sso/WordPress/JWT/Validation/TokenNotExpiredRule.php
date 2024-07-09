<?php

namespace GoDaddy\WordPress\MWC\Core\Auth\Sso\WordPress\JWT\Validation;

use DateTime;
use GoDaddy\WordPress\MWC\Common\Auth\JWT\Contracts\TokenContract;
use GoDaddy\WordPress\MWC\Common\Configuration\Configuration;
use GoDaddy\WordPress\MWC\Common\Validation\Contracts\ValidationRuleContract;

/**
 * Checks if the JWT is not expired.
 */
class TokenNotExpiredRule implements ValidationRuleContract
{
    /**
     * {@inheritDoc}
     */
    public function passes($input) : bool
    {
        if (! $input instanceof TokenContract) {
            return false;
        }

        // validate the token iat claim against a configured TTL (the library we are using already checks the exp)
        return (new DateTime('now'))->getTimestamp() < $input->getIssuedAt() + Configuration::get('auth.jwt.sso_ttl');
    }
}
