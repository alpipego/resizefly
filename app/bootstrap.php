<?php

use Alpipego\Resizefly\Admin\Licenses\LicensesPage;
use Alpipego\Resizefly\Admin\Licenses\LicensesSection;
use Alpipego\Resizefly\Common\Composer\Autoload\ClassLoader;
use Alpipego\Resizefly\Plugin;

require_once __DIR__.'/../src/Common/Composer/Autoload/ClassLoader.php';

$classLoader = new ClassLoader();
$classLoader->setPsr4('Alpipego\\Resizefly\\', realpath(__DIR__.'/../src/'));
$classLoader->register();

require_once __DIR__.'/functions.php';

add_action('plugins_loaded', function () use ($classLoader) {
    $plugin = new Plugin();
    $plugin->addDefiniton(__DIR__.'/config/plugin.php');
    $plugin->addDefiniton(__DIR__.'/config/queue.php');
    load_plugin_textdomain('resizefly', false, __DIR__.'/../languages');

    // Load compatibility fixes for other plugins
    $plugin->addDefiniton(__DIR__.'/config/compatibles.php');
    $plugin->getEarly();

    $file                      = realpath(__DIR__.'/../resizefly.php');
    $plugin['config.path']     = trailingslashit(plugin_dir_path($file));
    $plugin['config.url']      = plugin_dir_url($file);
    $plugin['config.basename'] = plugin_basename($file);
    $plugin['config.siteurl']  = apply_filters('resizefly/home_url', get_bloginfo('url'));
    $plugin['config.version']  = '4.0.0-beta3';

    // settings/filterable configuration values
    $plugin['options.cache.suffix']      = get_option('resizefly_resized_path', 'resizefly');
    $plugin['options.duplicates.suffix'] = apply_filters('resizefly/duplicate/dir', 'resizefly-duplicate');

    // set the cache path throughout the plugin
    $plugin['options.cache.path'] = function (Plugin $plugin) {
        return trailingslashit($plugin->get('Alpipego\Resizefly\Upload\Uploads')->getBasePath()).trim($plugin->get('options.cache.suffix'), DIRECTORY_SEPARATOR);
    };
    $plugin['options.cache.url']  = function (Plugin $plugin) {
        return trailingslashit($plugin->get('Alpipego\Resizefly\Upload\Uploads')->getBaseUrl()).trim($plugin->get('options.cache.suffix'), DIRECTORY_SEPARATOR);
    };
    // set the duplicates path
    $plugin['options.duplicates.path'] = function (Plugin $plugin) {
        return trailingslashit($plugin->get('Alpipego\Resizefly\Upload\Uploads')->getBasePath()).trim($plugin->get('options.duplicates.suffix'), DIRECTORY_SEPARATOR);
    };

    $plugin->offsetSet('loader', $classLoader);

    // filter for addons to register themselves
    $plugin->offsetSet('addons', apply_filters('resizefly/addons', []));

    foreach ($plugin->get('addons') as $addonName => $addon) {
        add_filter('resizefly/addons/'.$addonName, function () use ($plugin) {
            return $plugin;
        });
    }

    if (! empty($plugin->get('addons'))) {
        // add licenses for add-on
        $plugin->offsetSet('Licenses', function (Plugin $plugin) {
            return new LicensesSection(
                new LicensesPage(),
                $plugin->get('config.path'),
                $plugin->get('addons')
            );
        });
    }

    // Add own implementation to image editors
    add_filter('wp_image_editors', function (array $editors) {
        return array_merge(apply_filters('resizefly/image_editors', [
            '\Alpipego\Resizefly\Image\EditorImagick',
            '\Alpipego\Resizefly\Image\EditorGD',
        ]), $editors);
    });

    if (is_admin()) {
        $plugin->addDefiniton(__DIR__.'/config/admin.php');

        require_once __DIR__.'/actions/activation.php';

        // add compatibility fixes that are only needed in admin
        $plugin->addDefiniton(__DIR__.'/config/compatibles-admin.php');
    }

    // save options to retrieve them on uninstall
    update_option('resizefly_options', $plugin->get('options'), false);

    $plugin->run();

    if (version_compare($plugin->get('config.version'), get_option('resizefly_version')) !== 0) {
        require_once __DIR__.'/queue-tables.php';

        update_option('resizefly_version', $plugin->get('config.version'));
    }

    // register user added image sizes
    require_once __DIR__.'/actions/after-setup-theme.php';

    // check if image size in attachment metadata
    require_once __DIR__.'/actions/wp-get-attachment-src.php';

    // handle image resizing
    require_once __DIR__.'/actions/template-redirect.php';

    // fix wrong attachment date
    require_once __DIR__.'/actions/media-send-to-editor.php';
});
