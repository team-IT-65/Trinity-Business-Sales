<?php

namespace GoDaddy\WordPress\MWC\Core\Features\Stripe\Exceptions;

use GoDaddy\WordPress\MWC\Common\Exceptions\SentryException;

/**
 * Exception to be thrown when onboarding webhook fails.
 */
class FailedOnboardingWebhookException extends SentryException
{
}
