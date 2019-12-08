<?php

namespace Alpipego\Resizefly\Admin;

/**
 * Interface AdminInterface.
 */
interface PageInterface
{
    public function run();

    /**
     * @return string
     */
    public function getId();

    /**
     * @return string
     */
    public function getSlug();

    /**
     * @return array
     */
    public function localize(array $toLocalize);
}
