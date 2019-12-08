<?php

namespace Alpipego\Resizefly\Admin\Cache;

use Alpipego\Resizefly\Admin\AbstractOption;
use Alpipego\Resizefly\Admin\OptionInterface;
use Alpipego\Resizefly\Admin\PageInterface;

class RemoveResizedField extends AbstractOption implements OptionInterface
{
    public function __construct(PageInterface $page, $section, $pluginPath)
    {
        $this->optionsField = [
            'id'    => 'resizefly_remove_resized',
            'title' => esc_attr__('Remove All Resized Images', 'resizefly'),
            'args'  => ['class' => 'hide-if-no-js'],
        ];
        $this->localize($page);
        parent::__construct($page, $section, $pluginPath);
    }

    public function callback()
    {
        $this->includeView($this->optionsField['id'], $this->optionsField);
    }

    public function sanitize($value)
    {
        return $value;
    }

    private function localize(PageInterface $page)
    {
        $page->localize([
            'resized_id' => $this->optionsField['id'],
        ]);
    }
}
