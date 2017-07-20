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
	 * @var string $namespace namespace to load
	 */
	private $namespace;

    /**
     * Autoload constructor.
     *
     * @param string $dir pass dir, default to current dir
     * @param string $namespace
     *
     * @internal param string $ns pass namespace, default to current namespace
     */
	public function __construct( $dir = __DIR__, $namespace = __NAMESPACE__ ) {
        $this->dir       = $dir;
        $this->namespace = $namespace;
        $this->load();
    }

	/**
	 * load classes
	 */
	public function load() {
        spl_autoload_register( function ( $class ) {
            $class = ltrim( $class, '\\' );

            if (strpos( $class, $this->namespace ) !== 0 ) {
                return;
            }

            $class = str_replace( $this->namespace, '', $class );
            $path  = $this->dir . str_replace( '\\', DIRECTORY_SEPARATOR, $class ) . '.php';
            require_once $path;
        } );
    }
}
