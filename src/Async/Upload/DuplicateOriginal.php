<?php

namespace Alpipego\Resizefly\Async\Upload;

use Alpipego\Resizefly\Async\Job;
use Alpipego\Resizefly\Upload\DuplicateOriginal as Sync_DuplicateOriginal;

class DuplicateOriginal extends Job
{
    /**
     * @var string
     */
    protected $image;
    /**
     * @var Sync_DuplicateOriginal
     */
    protected $duplicate;

    public function __construct(Sync_DuplicateOriginal $duplicate, $image)
    {
        $this->image     = $image;
        $this->duplicate = $duplicate;
    }

    public function handle()
    {
        $this->duplicate->rebuild($this->image);
    }
}
