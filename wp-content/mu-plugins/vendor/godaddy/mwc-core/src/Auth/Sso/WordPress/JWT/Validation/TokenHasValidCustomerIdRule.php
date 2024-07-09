<?php

namespace GoDaddy\WordPress\MWC\Core\Auth\Sso\WordPress\JWT\Validation;

use GoDaddy\WordPress\MWC\Common\Platforms\PlatformRepositoryFactory;
use GoDaddy\WordPress\MWC\Common\Validation\Contracts\ValidationRuleContract;
use GoDaddy\WordPress\MWC\Core\Auth\Sso\WordPress\JWT\Contracts\SsoTokenContract;

/**
 * Checks if the customer ID from JWT and the one from the site match.
 */
class TokenHasValidCustomerIdRule implements ValidationRuleContract
{
    /**
     * {@inheritDoc}
     */
    public function passes($input) : bool
    {
        if ($input instanceof SsoTokenContract) {
            return ! empty($customerId = $input->getCustomerId())
                && PlatformRepositoryFactory::getNewInstance()->getPlatformRepository()->getGoDaddyCustomerId() === $customerId;
        }

        return false;
    }
}
