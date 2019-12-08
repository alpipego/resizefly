<?php

namespace Alpipego\Resizefly\Image;

use WP_Image_Editor;

/**
 * Wrapper for `WP_Image_Editor`.
 */
final class EditorWrapper implements EditorWrapperInterface
{
    /**
     * @var WP_Image_Editor
     */
    private $editor;

    /**
     * inject image editor.
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

    public function resizeImage($width, $height, $density, $focalX, $focalY)
    {
        $origWidth  = $this->getWidth();
        $origHeight = $this->getHeight();
        $factor     = max($width / $origWidth, $height / $origHeight);

        if ($density > 0) {
            list($quality, $width, $height) = $this->parseDensity($width, $height, $density);
            $factor                         = $factor * $density;
            // make sure not to request an image larger than the original
            if ($width > $origWidth || $height > $origHeight) {
                $ratio = $width / $height;
                if ($ratio === 1) {
                    $width = $height = min($origWidth, $origHeight);
                } else {
                    if ($width > $origWidth && $height > $origHeight) {
                        $height = $origWidth / $ratio * ($origHeight / $origWidth);
                        $width  = $origHeight / $ratio * ($origWidth / $origHeight);
                    } elseif ($width > $origWidth) {
                        $width  = $origWidth;
                        $height = $width * $height / $width;
                    } elseif ($height > $origHeight) {
                        $height = $origHeight;
                        $width  = $height * $ratio;
                    }
                }

                $factor = max($width / $origWidth, $height / $origHeight);
            }

            $this->editor->set_quality($quality);
        }

        $srcX       = round(($origWidth - $width / $factor) * $focalX / 100);
        $srcY       = round(($origHeight - $height / $factor) * $focalY / 100);

        return $this->editor->crop($srcX, $srcY, round($width / $factor), round($height / $factor), $width, $height);
    }

    public function getQuality()
    {
        return (int) $this->editor->get_quality();
    }

    public function saveImage($file)
    {
        do_action('resizefly/before_save', $file, $this->editor);

        return $this->editor->save($file);
    }

    /**
     * Stream the image.
     *
     * @param string $image
     */
    public function streamImage($image = '')
    {
        $cacheAge = apply_filters('resizefly/cache_age', 31536000);
        http_response_code(200);
        header('HTTP/1.1 200 OK');
        header('Pragma: public');
        header('Cache-Control: max-age='.$cacheAge.', public');
        header('Expires: '.gmdate('D, d M Y H:i:s \G\M\T', time() + $cacheAge));

        if (empty($image) && method_exists($this->editor, 'getImageSize')) {
            header('Content-Length: '.$this->editor->getImageSize());
        }

        do_action('resizefly/before_stream', $image, $this->editor);
        $this->editor->stream();
        exit;
    }

    /**
     * Parse density and set quality, width and height accordingly.
     *
     * @param int $width
     * @param int $height
     * @param int $density
     *
     * @return array
     *               0 => int $quality
     *               1 => int $width
     *               2 => int $height
     */
    private function parseDensity($width, $height, $density)
    {
        $width   = $width * $density;
        $height  = $height * $density;
        $quality = (int) apply_filters('resizefly/image/hidpi_quality', 70, $density);
        if (1 === $density) {
            $quality = $this->getQuality();
        }

        return array_map('intVal', [$quality, $width, $height]);
    }
}
