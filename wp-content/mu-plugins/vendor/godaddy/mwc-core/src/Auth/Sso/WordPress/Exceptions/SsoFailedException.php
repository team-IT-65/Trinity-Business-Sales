<?php

namespace GoDaddy\WordPress\MWC\Core\Auth\Sso\WordPress\Exceptions;

use GoDaddy\WordPress\MWC\Common\Exceptions\SentryException;
use GoDaddy\WordPress\MWC\Common\Traits\CanGetNewInstanceTrait;
use Throwable;

/**
 * {@see SentryException} thrown when SSO fails.
 *
 * @method static SsoFailedException getNewInstance(string $message, int $code = 500, ?Throwable $previous = null)
 */
class SsoFailedException extends SentryException
{
    use CanGetNewInstanceTrait;

    /**
     * Constructor.
     *
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(string $message, int $code = 500, ?Throwable $previous = null)
    {
        parent::__construct($message, $previous);

        $this->code = $code;
    }
}
