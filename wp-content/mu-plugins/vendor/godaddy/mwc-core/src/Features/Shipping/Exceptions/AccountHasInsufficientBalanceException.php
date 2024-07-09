<?php

namespace GoDaddy\WordPress\MWC\Core\Features\Shipping\Exceptions;

use GoDaddy\WordPress\MWC\Shipping\Exceptions\ShippingException;

class AccountHasInsufficientBalanceException extends ShippingException
{
    protected $errorCode = 'ACCOUNT_HAS_INSUFFICIENT_BALANCE';
}
