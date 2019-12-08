<?php

namespace Alpipego\Resizefly\Admin\Cache;

use Alpipego\Resizefly\Admin\AbstractOptionsSection;
use Alpipego\Resizefly\Admin\OptionsSectionInterface;
use Alpipego\Resizefly\Admin\PageInterface;

/**
 * Class BasicOptionsSection.
 */
final class CacheSection extends AbstractOptionsSection implements OptionsSectionInterface
{
    /**
     * BasicOptionsSection constructor.
     *
     * {@inheritdoc}
     */
    public function __construct(PageInterface $page, $pluginPath)
    {
        $this->optionsGroup = [
            'id'    => 'resizefly_cache',
            'title' => __('Cache Settings', 'resizefly'),
        ];
        parent::__construct($page, $pluginPath);
    }

    /**
     * Callback for section - include the view.
     */
    public function callback()
    {
        $this->includeView($this->optionsGroup['id'], $this->optionsGroup);
    }
}
