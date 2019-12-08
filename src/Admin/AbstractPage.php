<?php

namespace Alpipego\Resizefly\Admin;

abstract class AbstractPage implements PageInterface
{
    protected $localized = [];

    public function localize(array $toLocalize)
    {
        return $this->localized = array_merge_recursive($this->localized, $toLocalize);
    }
}
