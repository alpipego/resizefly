<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 05/17/16
 * Time: 12:23 PM
 */

namespace Alpipego\DynamicImage;

use ArrayAccess;

class Plugin implements ArrayAccess {
    protected $contents;

    public function __construct() {
        $this->contents = [];
    }

    public function offsetSet( $offset, $value ) {
        $this->contents[$offset] = $value;
    }

    public function offsetExists($offset) {
        return isset( $this->contents[$offset] );
    }

    public function offsetUnset($offset) {
        unset( $this->contents[$offset] );
    }

    public function offsetGet($offset) {
        if( is_callable($this->contents[$offset]) ){
            return call_user_func( $this->contents[$offset], $this );
        }
        return isset( $this->contents[$offset] ) ? $this->contents[$offset] : null;
    }

    public function run(){
        foreach( $this->contents as $key => $content ){ // Loop on contents
            if( is_callable($content) ){
                $content = $this[$key];
            }
            if( is_object( $content ) ){
                $reflection = new ReflectionClass( $content );
                if( $reflection->hasMethod( 'run' ) ){
                    $content->run(); // Call run method on object
                }
            }
        }
    }
}
