<?php

namespace GoDaddy\WordPress\Plugins\Launch\Dependencies\Illuminate\Contracts\Container;

use Exception;
use GoDaddy\WordPress\Plugins\Launch\Dependencies\Psr\Container\ContainerExceptionInterface;

class CircularDependencyException extends Exception implements ContainerExceptionInterface
{
    //
}
