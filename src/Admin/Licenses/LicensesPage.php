<?php

namespace Alpipego\Resizefly\Admin\Licenses;

use Alpipego\Resizefly\Admin\PageInterface;

class LicensesPage implements PageInterface
{
    const ID = 'resizefly_licenses';

    public function run()
    {
    }

    /**
     * @return string
     */
    public function getId()
    {
        return self::ID;
    }

    /**
     * @return string
     */
    public function getSlug()
    {
        return self::ID;
    }

    public function localize(array $toLocalize)
    {
        return [];
    }
}
