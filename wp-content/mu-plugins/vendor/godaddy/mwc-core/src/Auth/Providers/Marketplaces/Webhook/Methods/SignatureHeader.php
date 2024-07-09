<?php

namespace GoDaddy\WordPress\MWC\Core\Auth\Providers\Marketplaces\Webhook\Methods;

use GoDaddy\WordPress\MWC\Common\Traits\CanGetNewInstanceTrait;
use GoDaddy\WordPress\MWC\Core\Auth\Providers\Marketplaces\Webhook\Models\Credentials;

/**
 * Authentication method for incoming webhook requests from GDM.
 */
class SignatureHeader
{
    use CanGetNewInstanceTrait;

    /** @var string */
    const HEADER_NAME = 'HTTP_X_SB_SIGNATURE';

    /** @var Credentials */
    protected $credentials;

    /**
     * @param Credentials $credentials a credentials object that holds the customer ID and venture ID for the site
     */
    public function __construct(Credentials $credentials)
    {
        $this->credentials = $credentials;
    }

    /**
     * Generates a signature by hashing the payload.
     *
     * @see AbstractWebhookSubscriber::validate()
     *
     * @param string $encodedPayload The JSON encoded request payload.
     * @return string
     */
    public function getSignature(string $encodedPayload) : string
    {
        return hash_hmac('sha256', $encodedPayload, $this->credentials->getKey(), false);
    }
}
