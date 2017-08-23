<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 26/09/16
 * Time: 18:05
 */

namespace Alpipego\Resizefly\Upload;

use Alpipego\Resizefly\Image\EditorImagick;
use WP_Image_Editor;
use Imagick;

/**
 * Class DuplicateOriginal
 * @package Alpipego\Resizefly\Upload
 */
class DuplicateOriginal
{
    /**
     * @var array
     */
    private $uploads;

    /**
     * @var int
     */
    private $recursion = 0;

    /**
     * DuplicateOriginal constructor.
     *
     * @param UploadsInterface $uploads
     * @param string $duplicateDir
     */
    public function __construct(UploadsInterface $uploads, $duplicateDir)
    {
        $this->uploads = $uploads;
        $this->path    = trailingslashit(trailingslashit($this->uploads->getBasePath()) . $duplicateDir);
    }

    /**
     * DI run method
     * register hooks/filters
     */
    public function run()
    {
        if ($this->dirExists()) {
            add_filter('wp_generate_attachment_metadata', [$this, 'generateMeta'], 11, 2);
        }

        add_action('delete_attachment', [$this, 'delete']);
    }

    /**
     * Check if the directory exists (and is writeable), try to create it
     * TODO this should not at all be here but, in Admin | only quickly check if writeable
     *
     * @return bool
     */
    private function dirExists()
    {
        $permissions = is_writeable($this->path);

        if ( ! is_dir($this->path) && $permissions) {
            $permissions = mkdir($this->path, 0755, true);
        }

        if ( ! $permissions) {
            add_action('admin_init', function () {
                add_action('admin_notices', function () {
                    echo '<div class="error"><p>';
                    printf(__('The directory %s is not writeable by Resizefly. Please correct the permissions.',
                        'resizefly'
                    ), '<code>' . $this->path . '</code>'
                    );
                    echo '</p></div>';
                }
                );
            }
            );
        }

        return $permissions;
    }

    /**
     * Rebuild the image
     *
     * @param string $image image URL
     */
    public function rebuild($image)
    {
        $this->create($image);
    }

    /**
     * Duplicate the image
     * TODO this needs a refactor and should be split into smaller methods
     *
     * @param string $image image URL
     *
     * @return bool
     */
    protected function create($image)
    {
        $editor = wp_get_image_editor($image);
        if ( ! is_wp_error($editor)) {
            $duplicate = str_replace(trailingslashit($this->uploads->getBasePath()), $this->path, $image);

            if ((bool) apply_filters('resizefly_smaller_image', true) && $editor instanceof EditorImagick) {
                $sizes  = $editor->get_size();
                $larger = false;
                foreach ($sizes as $size) {
                    if ($size > (int) apply_filters('resizefly_smaller_image_threshold', 1200)) {
                        $larger = true;
                        break;
                    }
                }
                if ($larger && $this->calculateMemory($sizes, $editor)) {
                    $editor->blurImage(1, .5);
                }
            }

            $editor->set_quality(70);

            if ($editor instanceof EditorImagick) {
                $editor->stripImage();
            }

            // crop the image
            $longEdge = (int) apply_filters('resizefly_smaller_image_long_edge', 2560);
            if ($longEdge > 0) {
                $editor->resize($longEdge, $longEdge);
            }
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
                        $this->recursion++;
                        $this->create($image);
                    }
                }
            }

            return ! is_wp_error($save);
        }

        return false;
    }

    /**
     * Calculate the memory Imagick will need based on amount of pixels
     *
     * @param array $sizes ['width', 'height']
     * @param EditorImagick $editor
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
     * Try tweaking the resources to save an image (that could not be saved before)
     *
     * @param EditorImagick $editor
     *
     * @return bool
     */
    private function trySettingResources(EditorImagick $editor)
    {
        $editor->setResourceLimit(Imagick::RESOURCETYPE_AREA, 1);

        return true;
    }

    /**
     * Action method
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
     * Delete the duplicate if original is deleted
     *
     * @param integer $id Attachment Id
     */
    public function delete($id)
    {
        $file = str_replace(trailingslashit($this->uploads->getBasePath()), $this->path, get_attached_file($id));
        if (file_exists($file)) {
            unlink($file);
        }
    }
}
