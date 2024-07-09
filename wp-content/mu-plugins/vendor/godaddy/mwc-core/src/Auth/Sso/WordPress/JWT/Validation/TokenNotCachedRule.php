<?php

namespace GoDaddy\WordPress\MWC\Core\Auth\Sso\WordPress\JWT\Validation;

use GoDaddy\WordPress\MWC\Common\Exceptions\SentryException;
use GoDaddy\WordPress\MWC\Common\Validation\Contracts\ValidationRuleContract;
use GoDaddy\WordPress\MWC\Core\Auth\Sso\WordPress\JWT\Cache\CacheAuthJwt;
use GoDaddy\WordPress\MWC\Core\Auth\Sso\WordPress\JWT\Contracts\SsoTokenContract;

/**
 * Checks if the token is cached.
 *
 * If the token exists in the cache then it has already been used, and we should reject it to prevent a replay attack.
 */
class TokenNotCachedRule implements ValidationRuleContract
{
    /**
     * {@inheritDoc}
     */
    public function passes($input) : bool
    {
        if ($input instanceof SsoTokenContract) {
            if (CacheAuthJwt::getNewInstance($input->getJti())->get()) {
                SentryException::getNewInstance('Duplicate SSO token usage detected.');

                return false;
            }

            return true;
        }

        return false;
    }
}
