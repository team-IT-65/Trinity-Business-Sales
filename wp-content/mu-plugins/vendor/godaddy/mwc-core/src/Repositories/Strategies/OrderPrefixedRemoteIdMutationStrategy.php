<?php

namespace GoDaddy\WordPress\MWC\Core\Repositories\Strategies;

use GoDaddy\WordPress\MWC\Core\Repositories\Strategies\Abstracts\AbstractPrefixedRemoteIdMutationStrategy;

class OrderPrefixedRemoteIdMutationStrategy extends AbstractPrefixedRemoteIdMutationStrategy
{
    protected string $prefix = 'Order';
}
