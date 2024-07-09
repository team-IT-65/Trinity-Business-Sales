<?php

namespace GoDaddy\WordPress\MWC\Core\Auth\Sso\WordPress\Enums;

use GoDaddy\WordPress\MWC\Core\Traits\EnumTrait;

/**
 * Type of authentication token (see `auth` in below article).
 * @link https://godaddy-corp.atlassian.net/wiki/spaces/AUTH/pages/89653651/Token+Claims
 */
class TokenAuthType
{
    use EnumTrait;

    /** @var string employee, shopper, and pass user logins */
    public const Basic = 'basic';

    /** @var string employee to shopper impersonation */
    public const E2S = 'e2s';

    /** @var string shopper to shopper impersonation */
    public const S2S = 's2s';
}
