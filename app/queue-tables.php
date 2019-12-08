<?php

require_once ABSPATH.'wp-admin/includes/upgrade.php';

/** @var wpdb $wpdb */
$wpdb            = $GLOBALS['wpdb'];
$before          = $wpdb->hide_errors();
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
