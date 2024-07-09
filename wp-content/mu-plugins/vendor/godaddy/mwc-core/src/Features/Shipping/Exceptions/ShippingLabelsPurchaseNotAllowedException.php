<?php

namespace GoDaddy\WordPress\MWC\Core\Features\Shipping\Exceptions;

use GoDaddy\WordPress\MWC\Shipping\Exceptions\ShippingException;

class ShippingLabelsPurchaseNotAllowedException extends ShippingException
{
    protected $errorCode = 'ACCOUNT_NOT_ALLOWED_TO_PURCHASE_LABELS';
}
