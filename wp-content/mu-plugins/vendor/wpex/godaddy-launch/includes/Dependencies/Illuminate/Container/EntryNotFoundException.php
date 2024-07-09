<?php

namespace GoDaddy\WordPress\Plugins\Launch\Dependencies\Illuminate\Container;

use Exception;
use GoDaddy\WordPress\Plugins\Launch\Dependencies\Psr\Container\NotFoundExceptionInterface;

class EntryNotFoundException extends Exception implements NotFoundExceptionInterface
{
    //
}
