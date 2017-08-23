<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 05/20/16
 * Time: 12:07 PM
 */

namespace Alpipego\Resizefly\Image;

use WP_Image_Editor;

/**
 * Wrapper for `WP_Image_Editor`
 * @package Alpipego\Resizefly\Image
 */
final class EditorWrapper implements EditorWrapperInterface
{
    /**
     * @var WP_Image_Editor
     */
    private $editor;

    /**
     * inject image editor
     *
     * @param WP_Image_Editor $editor
     */
    public function __construct(WP_Image_Editor $editor)
    {
        $this->editor = $editor;
    }

    public function getRatio($aspect)
    {
        if (in_array($aspect, ['width', 'w'])) {
            return $this->getWidth() / $this->getHeight();
        }

        if (in_array($aspect, ['height', 'h'])) {
            return $this->getHeight() / $this->getWidth();
        }

        return 1;
    }

    public function getWidth()
    {
        return (int) $this->editor->get_size()['width'];
    }

    public function getHeight()
    {
        return (int) $this->editor->get_size()['height'];
    }

    public function resizeImage($width, $height, $focalX, $focalY)
    {
        $origWidth  = $this->getWidth();
        $origHeight = $this->getHeight();
        $ratio      = max($width / $origWidth, $height / $origHeight);
        $srcX       = round(($origWidth - $width / $ratio) * $focalX / 100);
        $srcY       = round(($origHeight - $height / $ratio) * $focalY / 100);

        return $this->editor->crop($srcX, $srcY, $width / $ratio, $height / $ratio, $width, $height);
    }

    public function saveImage($file)
    {
        do_action('resizefly_before_save', $file, $this->editor);

        return $this->editor->save($file);
    }

    /**
     * Stream the image
     *
     * @return void
     */
    public function streamImage()
    {
        $cacheAge = apply_filters('resizefly_cache_age', 31536000);
        http_response_code(200);
        header('HTTP/1.1 200 OK');
        header('Pragma: public');
        header('Cache-Control: max-age=' . $cacheAge . ', public');
        header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + $cacheAge));

        if (method_exists($this->editor, 'getImageBlob')) {
            header('Content-Length: ' . strlen($this->editor->getImageBlob()));
        }
        $this->editor->stream();
        exit;
    }

    public function getQuality()
    {
        return (int) $this->editor->get_quality();
    }
}
