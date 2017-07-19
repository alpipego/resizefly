<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 25.06.2017
 * Time: 12:12
 */

namespace Alpipego\Resizefly\Admin\Sizes;


use Alpipego\Resizefly\Admin\AbstractOptionsSection;
use Alpipego\Resizefly\Admin\OptionsSectionInterface;
use Alpipego\Resizefly\Admin\PageInterface;

class RegisteredSizesSection extends AbstractOptionsSection implements OptionsSectionInterface
{

    /**
     * RegisteredSizesSection constructor.
     *
     * @param PageInterface $page
     * @param string $pluginPath
     */
    public function __construct(PageInterface $page, $pluginPath)
    {
        $this->optionsGroup = [
            'id'   => 'resizefly_registered_sizes',
            'title' => __('Registered Sizes Settings', 'resizefly'),
        ];
        parent::__construct($page, $pluginPath);
    }

    /**
     * Callback function
     *
     * @return void
     */
    function callback()
    {
        $this->includeView($this->optionsGroup['id'], $this->optionsGroup);
    }
}
