<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 05/17/16
 * Time: 12:23 PM
 */

namespace Alpipego\Resizefly;

use Alpipego\Resizefly\Pimple\Container;
use ReflectionClass;

class Plugin extends Container {

    public function run() {
        foreach ( $this->values as $key => $content ) { // Loop on contents
            $content = $this[ $key ];

            if ( is_object( $content ) ) {
                $reflection = new ReflectionClass( $content );
                if ( $reflection->hasMethod( 'run' ) ) {
                    $content->run(); // Call run method on object
                }
            }
        }
    }

    public function loadTextdomain($dir) {
        load_plugin_textdomain('resizefly', false, $dir);
    }
}
