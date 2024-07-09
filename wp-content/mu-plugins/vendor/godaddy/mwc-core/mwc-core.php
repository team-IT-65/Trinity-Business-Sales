<?php
/**
 * Plugin name: MWC Core.
 */

namespace GoDaddy\WordPress\MWC\Core;

use Exception;
use GoDaddy\WordPress\MWC\Common\Exceptions\SentryException;
use GoDaddy\WordPress\MWC\Core\Plugin\SystemPluginPatchLoader;

if (version_compare(PHP_VERSION, '7.4', '>=')) {
    require_once __DIR__.'/vendor/autoload.php';
}

// load the MWC core package
add_action('plugins_loaded', static function () {
    try {
        if (version_compare(PHP_VERSION, '7.4', '>=')) {
            SystemPluginPatchLoader::getInstance()->load();
        }
    } catch (SentryException $exception) {
        // avoid logging Sentry exceptions twice
    } catch (Exception $exception) {
        new SentryException("Failed to get core instance: {$exception->getMessage()}", $exception);
    }
});
