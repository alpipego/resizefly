<?php

namespace Alpipego\Resizefly\Upload;

use Alpipego\Resizefly\Image\EditorImagick;
use Imagick;

/**
 * Class DuplicateOriginal.
 */
class DuplicateOriginal
{
    /**
     * @var UploadsInterface
     */
    private $uploads;

    /**
     * @var int
     */
    private $recursion = 0;

    /**
     * @var bool
     */
    private $errorAdded = false;

    /**
     * @var string
     */
    private $path;

    /**
     * DuplicateOriginal constructor.
     *
     * @param string $duplicateDir
     */
    public function __construct(UploadsInterface $uploads, $duplicateDir)
    {
        $this->uploads = $uploads;
        $this->path    = trailingslashit(trailingslashit($this->uploads->getBasePath()).$duplicateDir);
    }

    /**
     * DI run method
     * register hooks/filters.
     */
    public function run()
    {
        if ($this->dirExists()) {
            add_filter('wp_generate_attachment_metadata', [$this, 'generateMeta'], 11, 2);
        }

        add_action('delete_attachment', [$this, 'delete']);
    }

    /**
     * Rebuild the image.
     *
     * @param string $image image URL
     */
    public function rebuild($image)
    {
        $this->create($image);
    }

    /**
     * Action method.
     *
     * @param $metadata
     * @param $attId
     *
     * @return mixed
     */
    public function generateMeta($metadata, $attId)
    {
        $this->create(get_attached_file($attId));

        return $metadata;
    }

    /**
     * Delete the duplicate if original is deleted.
     *
     * @param int $id Attachment Id
     */
    public function delete($id)
    {
        $file = str_replace(trailingslashit($this->uploads->getBasePath()), $this->path, get_attached_file($id));
        if (file_exists($file)) {
            unlink($file);
        }
    }

    /**
     * Duplicate the image.
     *
     * @param string $image image URL
     *
     * @return bool
     */
    protected function create($image)
    {
        $editor = wp_get_image_editor($image);
        if (is_wp_error($editor) || ! (bool) apply_filters('resizefly/duplicate', true)) {
            return false;
        }

        $duplicate = str_replace(trailingslashit($this->uploads->getBasePath()), $this->path, $image);

        if ($editor instanceof EditorImagick) {
            $sizes  = $editor->get_size();
            $larger = false;
            foreach ($sizes as $size) {
                if ($size > (int) apply_filters('resizefly/duplicate/threshold', 1200)) {
                    $larger = true;
                    break;
                }
            }
            if ($larger && $this->calculateMemory($sizes, $editor)) {
                $editor->blurImage(1, .5);
            }
        }

        // resize the image
        $longEdge = (int) apply_filters('resizefly/duplicate/long_edge', 2560);
        $longEdge = $longEdge > 0 ? $longEdge : 2560;
        $longEdge = $longEdge > max($editor->get_size()) ? max($editor->get_size()) : $longEdge;
        $editor->resize($longEdge, $longEdge);

        if ($editor instanceof EditorImagick) {
            $editor->stripImage();
            $editor->setColorspace(\Imagick::COLORSPACE_SRGB);
        }

        // set quality
        $quality = (int) apply_filters('resizefly/duplicate/quality', $editor->get_quality());
        $quality = $quality > 0 ? $quality : $editor->get_quality();
        $editor->set_quality($quality);

        // check if image could be saved
        $save = $editor->save($duplicate);
        if (is_wp_error($save)) {
            // delete the zero byte file
            if (file_exists($duplicate)) {
                unlink($duplicate);
            }

            if ($editor instanceof EditorImagick) {
                // try setting resources and try once more
                if ($this->trySettingResources($editor) && $this->recursion < 1) {
                    ++$this->recursion;
                    $this->create($image);
                }
            }
        }

        return ! is_wp_error($save);
    }

    /**
     * Check if the directory exists (and is writable), try to create it.
     *
     * @return bool
     */
    private function dirExists()
    {
        $dir = wp_mkdir_p($this->path) && wp_is_writable($this->path);

        if (! $dir && ! $this->errorAdded) {
            add_action('admin_init', function () {
                $this->errorAdded = true;
                add_action('admin_notices', function () {
                    echo '<div class="error"><p>';
                    printf(
                        __('The directory %s is not writeable by Resizefly. Please correct the permissions.', 'resizefly'),
                        '<code>'.$this->path.'</code>'
                    );
                    echo '</p></div>';
                });
            });
        }

        return $dir;
    }

    /**
     * Calculate the memory Imagick will need based on amount of pixels.
     *
     * @param array $sizes ['width', 'height']
     *
     * @return bool
     */
    private function calculateMemory(array $sizes, EditorImagick $editor)
    {
        $bytesImage   = $sizes['width'] * $sizes['height'] * 64;
        $bytesImagick = $editor->getResourceLimit(Imagick::RESOURCETYPE_MEMORY);

        return $bytesImage < $bytesImagick;
    }

    /**
     * Try tweaking the resources to save an image (that could not be saved before).
     *
     * @return bool
     */
    private function trySettingResources(EditorImagick $editor)
    {
        $editor->setResourceLimit(Imagick::RESOURCETYPE_AREA, 1);

        return true;
    }
}
