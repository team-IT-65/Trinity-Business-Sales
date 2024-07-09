<?php

namespace GoDaddy\WordPress\MWC\Core\Features\Shipping\Exceptions;

use GoDaddy\WordPress\MWC\Shipping\Exceptions\ShippingException;

class ShippingLabelVoidNotApprovedException extends ShippingException
{
    protected $errorCode = 'VOID_ATTEMPT_NOT_APPROVED';
}
