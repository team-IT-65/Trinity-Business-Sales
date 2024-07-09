<?php

namespace GoDaddy\WordPress\MWC\Core\Repositories\Strategies;

use GoDaddy\WordPress\MWC\Core\Repositories\Strategies\Abstracts\AbstractPrefixedRemoteIdMutationStrategy;

class LineItemPrefixedRemoteIdMutationStrategy extends AbstractPrefixedRemoteIdMutationStrategy
{
    protected string $prefix = 'LineItem';
}
