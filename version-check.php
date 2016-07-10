<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 15/06/16
 * Time: 19:11
 */

if ( version_compare( PHP_VERSION, '5.4', '<' ) ) {
	add_action( 'admin_notices', create_function( '', "echo '<div class=\"error\"><p>" . __( 'ResizeFly requires PHP 5.4 to function properly. Please upgrade PHP to use ResizeFly.', 'resizefly' ) . "</p></div>';" ) );
	\deactivate_plugins( __FILE__ );

	return;
}
