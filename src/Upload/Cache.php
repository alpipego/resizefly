<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 27/09/16
 * Time: 12:03
 */

namespace Alpipego\Resizefly\Upload;

use Alpipego\Resizefly\Admin\OptionInterface;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * Class Cache
 * @package Alpipego\Resizefly\Upload
 */
class Cache
{
    private $uploads;
    private $action;
    private $cachePath;
    private $filesize = 0;
    private $files = 0;
    private $addons;

    public function __construct(UploadsInterface $uploads, OptionInterface $field, $cachePath, $addons)
    {
        $this->uploads   = $uploads;
        $this->action    = $field->getId();
        $this->cachePath = $cachePath;
        $this->addons    = $addons;
    }

    public function run()
    {
        add_action('delete_attachment', [$this, 'purgeSingle']);
        add_action("wp_ajax_{$this->action}", [$this, 'ajaxCallback']);
    }

    public function purgeSingle($id)
    {
        $file = new SplFileInfo(get_attached_file($id));
        $path = str_replace($this->uploads->getBasePath(), $this->cachePath, $file->getPathInfo());
        try {
            $dir = new RecursiveDirectoryIterator($path);
        } catch (\Exception $e) {
            // probably the directory does not exist
            return false;
        }
        $match = sprintf(
            '/^%s-[0-9]+x[0-9]+\.%s$/i',
            $file->getBasename('.' . $file->getExtension()),
            $file->getExtension()
        );

        /** @var SplFileInfo $file */
        foreach ($dir as $file) {
            if (preg_match($match, $file->getBasename())) {
                unlink($file->getRealPath());
            }
        }
    }

    public function ajaxCallback()
    {
        $canDelete = current_user_can(apply_filters('resizefly/delete_attachment_cap', 'delete_posts'));
        if (check_ajax_referer($this->action) && $canDelete) {
            $freed = $this->purgeAll($this->cachePath);
            wp_send_json($freed);
        } else {
            wp_send_json('fail');
        }
    }

    /**
     * Purge ResizeFly cache - all (or smart)
     *
     * @param string $dir ResizeFly cache dir
     *
     * @return array
     *      'files' => int number of files cleared,
     *      'size' => float sum of freed space
     */
    private function purgeAll($dir)
    {
        $iterator   = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS));
        $smartPurge = filter_var($_POST['smart-purge'], FILTER_VALIDATE_BOOLEAN);
        if ($smartPurge) {
            $retain = $this->smartPurge();
        }
        foreach ($iterator as $path) {
            if (! $path->isDir()) {
                $file = $path->__toString();
                if ($smartPurge && preg_match($retain, $file)) {
                    continue;
                }
                $this->filesize += filesize($file);
                $this->files++;
                unlink($file);
            }
        }

        return ['files' => $this->files, 'size' => $this->filesize];
    }

    /**
     * Gather filesizes to keep
     *
     * @return string
     */
    private function smartPurge()
    {
        $retain   = $this->getThumbnails();
        $retain[] = $this->getLqir();

        $retain = array_unique(array_filter($retain));

        return '/-(' . implode('|', $retain) . ')\.(jpe?g|png|gif)$/i';
    }

    /**
     * Get built in thumbnail sizes
     *
     * @return array
     */
    private function getThumbnails()
    {
        $intermediate = get_intermediate_image_sizes();
        $sizes        = [];
        foreach (['thumbnail', 'medium'] as $size) {
            if (! in_array($size, $intermediate)) {
                print $size . ' does not exist';
                continue;
            }
            $regex = (int)get_option("{$size}_size_w") . 'x';
            $regex .= (bool)get_option("{$size}_crop") ? (int)get_option("{$size}_size_h") : '\d+?';
            $regex .= '@[1-9]';

            $sizes[$size] = $regex;
        }

        return $sizes;
    }

    /**
     * If lqir addon present, keep the currently set filesize
     *
     * @return string
     */
    private function getLqir()
    {
        if (! array_key_exists('lqir', $this->addons)) {
            return '';
        }

        return ((int)get_option('resizefly_lqir_size', apply_filters('resizefly/lqir/size', 150))) . 'x\d+?@0';
    }
}
