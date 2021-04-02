<?php

/**
 * Plugin Name: Resizefly
 * Description: Dynamically resize your images on the fly
 * Plugin URI:  https://resizefly.com/
 * Version:     4.0.0-beta6
 * Author:      alpipego
 * Author URI:  https://alpipego.com/
 * License:     MIT
 * License URI: https://opensource.org/licenses/MIT
 * Text Domain: resizefly
 * GitHub Plugin URI: https://github.com/alpipego/resizefly
 * GitHub Branch: master.
 */

// PHP 5.2 compatible version check
require_once dirname(__FILE__).'/version-check.php';
$check = new Resizefly_Version_Check();

if (! $check->errors()) {
    include_once dirname(__FILE__).'/app/bootstrap.php';
}
