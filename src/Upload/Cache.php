<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 27/09/16
 * Time: 12:03.
 */

namespace Alpipego\Resizefly\Upload;

use Alpipego\Resizefly\Async\Queue\Queue;
use Alpipego\Resizefly\Image\ImageInterface;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * Class Cache.
 */
final class Cache implements CacheInterface
{
    private $uploads;
    private $cachePath;
    private $queue;
    private $filesize = 0;
    private $files    = 0;
    private $addons;
    private $image;

    public function __construct(
        UploadsInterface $uploads,
        ImageInterface $image,
        Queue $queue,
        $cachePath,
        $addons
    ) {
        $this->uploads   = $uploads;
        $this->cachePath = $cachePath;
        $this->addons    = $addons;
        $this->image     = $image;
        $this->queue     = $queue;
    }

    public function purgeSingle($id, $deleteDuplicate = true)
    {
        $amount = 0;
        $file   = new SplFileInfo(get_attached_file($id));
        $path   = str_replace($this->uploads->getBasePath(), $this->cachePath, $file->getPathInfo());
        try {
            $dir = new RecursiveDirectoryIterator($path);
        } catch (\Exception $e) {
            // probably the directory does not exist
            return $amount;
        }

        if ($deleteDuplicate) {
            if (preg_match(
                '/(?<file>.*?)-(?<width>[0-9]+)x(?<height>[0-9]+)@(?<density>[0-3])\.(?<ext>jpe?g|png|gif)/i',
                wp_get_attachment_image_src($id)[0],
                $matches
            )) {
                $this->image->setImage($matches);
                $duplicate = $this->image->getDuplicatePath();
                if (file_exists($duplicate) && unlink($duplicate)) {
                    ++$amount;
                }
            }
        }

        $match = sprintf(
            '/^(?<file>%s)-(?<width>[0-9]+)x(?<height>[0-9]+)@(?<density>[0-3])\.(?<ext>%s)/i',
            $file->getBasename('.'.$file->getExtension()),
            $file->getExtension()
        );

        /** @var SplFileInfo $file */
        foreach ($dir as $file) {
            if (! $file->isFile()) {
                continue;
            }

            if (preg_match($match, $file->getBasename()) && unlink($file->getRealPath())) {
                ++$amount;
            }
        }

        return $amount;
    }

    public function purgeAll($smart = true)
    {
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->cachePath, RecursiveDirectoryIterator::SKIP_DOTS));

        if ($smart) {
            $retain = $this->smartPurge();
        }

        /** @var SplFileInfo $path */
        foreach ($iterator as $path) {
            if (! $path->isFile()) {
                continue;
            }

            $file = $path->__toString();
            if ($smart && preg_match($retain, $file)) {
                continue;
            }
            $this->filesize += filesize($file);
            ++$this->files;
            unlink($file);
        }

        return ['files' => $this->files, 'size' => $this->filesize];
    }

    public function warmUpAll()
    {
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->uploads->getBasePath(), RecursiveDirectoryIterator::SKIP_DOTS));

        /** @var SplFileInfo $path */
        foreach ($iterator as $path) {
            if (! $path->isFile()) {
                continue;
            }

            $this->warmUp((string) $path);
        }
    }

    public function warmUpSingle($file)
    {
        return $this->warmUp($file);
    }

    /**
     * Gather filesizes to keep.
     *
     * @return string
     */
    private function smartPurge()
    {
        $retain = (array) apply_filters('resizefly/cache/retain_sizes', $this->getThumbnails());
        $retain = array_unique(array_filter($retain));

        return '/-('.implode('|', $retain).')\.(jpe?g|png|gif)$/i';
    }

    /**
     * Get built in thumbnail sizes.
     *
     * @param bool $density
     *
     * @return array
     */
    private function getThumbnails($density = true)
    {
        static $sizes;
        if (! is_null($sizes)) {
            return $sizes;
        }

        $intermediate = get_intermediate_image_sizes();
        $sizes        = [];
        foreach (['thumbnail', 'medium'] as $size) {
            if (! in_array($size, $intermediate)) {
                continue;
            }
            $regex = (int) get_option("{$size}_size_w").'x';
            $regex .= (bool) get_option("{$size}_crop") ? (int) get_option("{$size}_size_h") : '\d+?';
            if ($density) {
                $regex .= '@[1-9]';
            }

            $sizes[$size] = $regex;
        }

        return $sizes;
    }

    private function warmUp($file)
    {
        $thumbnails = $this->getThumbnails(false);
        // if there are no registered thumbnail sizes return
        if (empty($thumbnails)) {
            return;
        }

        static $thumbRegex;
        if (empty($thumbRegex)) {
            $thumbRegex = '/-('.implode('|', $thumbnails).')\.(jpe?g|png|gif)$/i';
        }

        // if this is not a thumbnail size skip
        if (! preg_match($thumbRegex, $file)) {
            return;
        }

        // if this is not either in uploads directly or in a year/month based folder skip
        if (! preg_match(
            '%'.$this->uploads->getBasePath().'/(\d{4}/\d{2}/)?[^/]+\.(?:jpe?g|png|gif)$%',
            $file,
            $fragments
        )) {
            return;
        }

        // if this is an original skip
        $url = str_replace($this->uploads->getBasePath(), $this->uploads->getBaseUrl(), $file);
        preg_match(
            '/(?<file>.*?)-(?<width>[0-9]+)x(?<height>[0-9]+)(@(?<density>[0-3]))?\.(?<ext>jpe?g|png|gif)/i',
            $url,
            $matches
        );

        if ($this->image->setImage($matches)->getOriginalPath() === $file) {
            return;
        }

        $newFile = sprintf(
            '%s-%dx%d@%d.%s',
            str_replace($this->uploads->getBaseUrl(), $this->cachePath, $matches['file']),
            $matches['width'],
            $matches['height'],
            1,
            $matches['ext']
        );

        // skip if a file with the name already exists
        if (file_exists($newFile)) {
            return;
        }

        // if the dir can't be created skip
        if (! wp_mkdir_p($this->cachePath.'/'.$fragments[1])) {
            return;
        }

        // create duplicate
        $this->queue->resolve($this->duplicateOriginal, $this->image->getOriginalPath());

        // actually move the file
        copy($file, $newFile);
    }
}
