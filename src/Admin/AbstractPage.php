<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 08/10/16
 * Time: 13:59.
 */

namespace Alpipego\Resizefly\Admin;

class AbstractPage
{
    protected $localized = [];

    public function localize(array $toLocalize)
    {
        return $this->localized = array_merge_recursive($this->localized, $toLocalize);
    }
}
