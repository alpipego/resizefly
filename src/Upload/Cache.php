<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 27/09/16
 * Time: 12:03
 */

namespace Alpipego\Resizefly\Upload;

use Alpipego\Resizefly\Admin\OptionInterface;
use SplFileInfo;
use RecursiveDirectoryIterator;

class Cache
{
    private $uploads;
    private $action;
    private $cachePath;
    private $filesize = 0;
    private $files = 0;

    public function __construct(UploadsInterface $uploads, OptionInterface $field, $cachePath)
    {
        $this->uploads   = $uploads;
        $this->action    = $field->getId();
        $this->cachePath = $cachePath;
    }

    public function run()
    {
        add_action('delete_attachment', [$this, 'purgeSingle']);
        add_action("wp_ajax_{$this->action}", [$this, 'ajaxCallback']);
    }

    public function purgeSingle($id)
    {
        $file  = new SplFileInfo(get_attached_file($id));
        $path  = str_replace($this->uploads->getBasePath(), $this->cachePath, $file->getPathInfo());
        $dir   = new RecursiveDirectoryIterator($path);
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

    private function purgeAll($dir)
    {
        foreach (glob("{$dir}/*") as $file) {
            if (is_dir($file)) {
                $this->purgeAll($file);
            } else {
                $this->filesize += filesize($file);
                $this->files++;
                unlink($file);
            }
        }

        return ['files' => $this->files, 'size' => $this->filesize];
    }
}
