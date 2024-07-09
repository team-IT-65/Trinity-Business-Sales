<?php

namespace GoDaddy\WordPress\MWC\Core\Auth\Sso\WordPress\Services;

use GoDaddy\WordPress\MWC\Common\Traits\CanGetNewInstanceTrait;
use GoDaddy\WordPress\MWC\Core\Auth\Sso\WordPress\Enums\TokenAuthType;
use GoDaddy\WordPress\MWC\Core\Auth\Sso\WordPress\JWT\Contracts\SsoTokenContract;
use GoDaddy\WordPress\MWC\Core\Auth\Sso\WordPress\Services\Contracts\UserAssociationStrategyContract;

/**
 * This class is responsible for handling a user association strategy based on the SSO token in context.
 */
class UserAssociationStrategyFactory
{
    use CanGetNewInstanceTrait;

    /**
     * Gets the user association strategy for the given SSO token.
     *
     * @param SsoTokenContract $token
     * @return UserAssociationStrategyContract
     */
    public function getAssociationStrategy(SsoTokenContract $token) : UserAssociationStrategyContract
    {
        $authType = TokenAuthType::tryFrom($token->getAuthType());

        if ($authType === TokenAuthType::E2S) {
            return new CareAgentUserAssociationStrategy($token);
        }

        return new DefaultUserAssociationStrategy($token);
    }
}
