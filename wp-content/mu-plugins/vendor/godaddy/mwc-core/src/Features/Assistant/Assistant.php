<?php

namespace GoDaddy\WordPress\MWC\Core\Features\Assistant;

use GoDaddy\WordPress\MWC\Common\Configuration\Configuration;
use GoDaddy\WordPress\MWC\Common\Features\AbstractFeature;
use GoDaddy\WordPress\MWC\Common\Helpers\StringHelper;

class Assistant extends AbstractFeature
{
    /**
     * {@inheritDoc}
     */
    public static function getName() : string
    {
        return 'assistant';
    }

    /**
     * {@inheritdoc}
     */
    public function load() : void
    {
        $rootVendorPath = StringHelper::trailingSlash(Configuration::get('system_plugin.project_root').'/vendor');

        // Load plugin class file
        require_once $rootVendorPath.'godaddy/mwc-ai-assistant/assistant.php';
    }
}
