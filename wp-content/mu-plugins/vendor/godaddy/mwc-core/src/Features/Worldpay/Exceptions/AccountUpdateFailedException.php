<?php

namespace GoDaddy\WordPress\MWC\Core\Features\Worldpay\Exceptions;

use GoDaddy\WordPress\MWC\Common\Exceptions\SentryException;

/**
 * An exception to use when a Worldpay account update failed.
 *
 * This is logged to Sentry.
 */
class AccountUpdateFailedException extends SentryException
{
}
