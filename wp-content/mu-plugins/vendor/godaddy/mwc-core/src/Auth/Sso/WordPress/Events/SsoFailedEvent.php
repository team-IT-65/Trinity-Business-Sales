<?php

namespace GoDaddy\WordPress\MWC\Core\Auth\Sso\WordPress\Events;

use GoDaddy\WordPress\MWC\Common\Events\Contracts\EventBridgeEventContract;
use GoDaddy\WordPress\MWC\Common\Traits\CanGetNewInstanceTrait;
use GoDaddy\WordPress\MWC\Common\Traits\IsEventBridgeEventTrait;

/**
 * Event that should be triggered when something in the SSO flow fails.
 *
 * @method static static getNewInstance(string $reason)
 */
class SsoFailedEvent implements EventBridgeEventContract
{
    use CanGetNewInstanceTrait;
    use IsEventBridgeEventTrait;

    /** @var string message detailing the reason for the SSO failure */
    protected string $reason;

    /**
     * Event constructor.
     *
     * @param string $reason the reason for failure
     */
    public function __construct(string $reason)
    {
        $this->resource = 'sso';
        $this->action = 'failed';
        $this->reason = $reason;
    }

    /**
     * Gets a message with the reason for SSO failure.
     *
     * @return string
     */
    public function getReason() : string
    {
        return $this->reason;
    }

    /**
     * Adds the failure message to the data.
     *
     * @return array<string, mixed>
     */
    protected function buildInitialData() : array
    {
        $this->data['reason'] = $this->getReason();

        return $this->data;
    }
}
