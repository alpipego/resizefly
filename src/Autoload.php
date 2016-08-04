<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 05/13/16
 * Time: 2:32 PM
 */

namespace Alpipego\Resizefly;

/**
 * custom autoloader for now, available to add ons as well
 * @package Alpipego\Resizefly
 */
class Autoload {
	/**
	 * @var string $dir directory to load
	 */
	private $dir;
	/**
	 * @var string $ns namespace to load
	 */
	private $ns;

	/**
	 * Autoload constructor.
	 *
	 * @param string $dir pass dir, default to current dir
	 * @param string $ns pass namespace, default to current namespace
	 */
	public function __construct( $dir = __DIR__, $ns = __NAMESPACE__ ) {
        $this->dir = $dir;
        $this->ns  = $ns;
        $this->load();
    }

	/**
	 * load classes
	 */
	public function load() {
        \spl_autoload_register( function ( $class ) {
            $class = ltrim( $class, '\\' );

            if ( strpos( $class, $this->ns ) !== 0 ) {
                return;
            }

            $class = str_replace( $this->ns, '', $class );
            $path  = $this->dir . str_replace( '\\', DIRECTORY_SEPARATOR, $class ) . '.php';
            require_once $path;
        } );
    }
}
