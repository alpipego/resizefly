<?php

namespace Alpipego\Resizefly\Upload;

interface CacheInterface
{
    /**
     * @param int  $id              Post ID of attachment
     * @param bool $deleteDuplicate delete the duplicated original
     *
     * @return int amount of files deleted
     */
    public function purgeSingle($id, $deleteDuplicate = true);

    /**
     * Purge ResizeFly cache - all (or smart).
     *
     * @param bool $smart decide whether common files should be retained (duplicate and thumbnail)
     *
     * @return array
     *               'files' => int number of files cleared,
     *               'size' => float sum of freed space
     */
    public function purgeAll($smart);

    /**
     * Go through all uploads and warm the cache, i.e. add duplicate and thumbnail.
     */
    public function warmUpAll();

    /**
     * @param string $file Path to the image
     */
    public function warmUpSingle($file);
}
