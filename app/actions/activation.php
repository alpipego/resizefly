<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 1.11.2017
 * Time: 12:21.
 *
 * @var Plugin $plugin
 */

use Alpipego\Resizefly\Common\Psr\Container\ContainerExceptionInterface;
use Alpipego\Resizefly\Common\Psr\Container\NotFoundExceptionInterface;
use Alpipego\Resizefly\Plugin;

if (version_compare(get_option('resizefly_version'), $plugin['config.version'], '<')) {
    // try to move thumbnail files
    try {
        $plugin->get('Alpipego\Resizefly\Upload\Cache')->warmUpAll();
    } catch (NotFoundExceptionInterface $e) {
    } catch (ContainerExceptionInterface $e) {
    }

    require_once ABSPATH.'wp-admin/includes/upgrade.php';

    /** @var wpdb $wpdb */
    $wpdb = $GLOBALS['wpdb'];
    $before = $wpdb->hide_errors();
    $charset_collate = $wpdb->get_charset_collate();

    dbDelta(<<<JOBS
CREATE TABLE {$wpdb->prefix}rzf_queue_jobs (
id bigint(20) NOT NULL AUTO_INCREMENT,
job longtext NOT NULL,
attempts tinyint(3) NOT NULL DEFAULT 0,
reserved_at datetime DEFAULT NULL,
available_at datetime NOT NULL,
created_at datetime NOT NULL,
PRIMARY KEY  (id)
) $charset_collate;
JOBS
    );

    dbDelta(<<<FAILS
CREATE TABLE {$wpdb->prefix}rzf_queue_failures (
id bigint(20) NOT NULL AUTO_INCREMENT,
job longtext NOT NULL,
error text DEFAULT NULL,
failed_at datetime NOT NULL,
PRIMARY KEY  (id)
) $charset_collate;
FAILS
    );

    $wpdb->show_errors($before);

    // update the version option
    add_option('resizefly_version_initial', $plugin['config.version']);
    update_option('resizefly_version', $plugin['config.version']);
}
;
