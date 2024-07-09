<?php

namespace GoDaddy\WordPress\MWC\Core\Auth\Sso\WordPress\Events;

use GoDaddy\WordPress\MWC\Common\Events\Contracts\EventBridgeEventContract;
use GoDaddy\WordPress\MWC\Common\Traits\CanGetNewInstanceTrait;
use GoDaddy\WordPress\MWC\Common\Traits\IsEventBridgeEventTrait;

/**
 * Event that should be triggered when the SSO succeeds.
 */
class SsoSucceededEvent implements EventBridgeEventContract
{
    use CanGetNewInstanceTrait;
    use IsEventBridgeEventTrait;

    /**
     * Event constructor.
     */
    public function __construct()
    {
        $this->resource = 'sso';
        $this->action = 'login';
    }
}
