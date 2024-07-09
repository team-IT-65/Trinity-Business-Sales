<?php

namespace GoDaddy\WordPress\MWC\Core\Features;

use GoDaddy\WordPress\MWC\Core\Traits\EnumTrait;

/**
 * Holder class for features possible categories values.
 * TODO: switch it to ENUMS once the platform minimum requirements becomes PHP 8.x {nmolham 27-12-2021}.
 *
 * @see https://www.php.net/manual/en/language.enumerations.backed.php
 */
class Categories
{
    use EnumTrait;

    public const StoreManagement = 'Store Management';

    public const Marketing = 'Marketing and Messaging';

    public const Shipping = 'Shipping';

    public const Merchandising = 'Merchandising';

    public const ProductType = 'Product Type';

    public const CartCheckout = 'Cart and Checkout';

    public const Payments = 'Payments';
}
