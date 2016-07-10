<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 05/20/16
 * Time: 12:07 PM
 */

namespace Alpipego\Resizefly\Image;

use WP_Image_Editor;

class Editor {
    private $editor;

    public function __construct( WP_Image_Editor $editor ) {
        $this->editor = $editor;
    }

    public function getWidth() {
        return $this->getSize()['width'];
    }

    public function getHeight() {
        return $this->getSize()['height'];
    }

    private function getSize() {
        return $this->editor->get_size();
    }

    public function getRatio( $aspect ) {
        if ( in_array( $aspect, [ 'width', 'w' ] ) ) {
            return $this->getWidth() / $this->getHeight();
        } elseif ( in_array( $aspect, [ 'height', 'h' ] ) ) {
            return $this->getHeight() / $this->getWidth();
        }

        return 1;
    }

    public function resizeImage( $width, $height ) {
        return $this->editor->resize( $width, $height, true );
    }

    public function saveImage( $file ) {
        return $this->editor->save( $file );
    }

    protected function streamImage() {
        http_response_code( 200 );
        header( 'Pragma: public' );
        header( 'Cache-Control: max-age=86400, public' );
        header( 'Expires: ' . gmdate( 'D, d M Y H:i:s \G\M\T', time() + 86400 ) );
        $this->editor->stream();
    }

    public function __get( $name ) {
        if ( method_exists( $this, 'get' . ucfirst( $name ) ) ) {
            return call_user_func( [ $this, 'get' . ucfirst( $name ) ] );
        }

        return false;
    }


}
