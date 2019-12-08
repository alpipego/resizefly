<?php

namespace Alpipego\Resizefly\Admin\Licenses;

use Alpipego\Resizefly\Admin\AbstractOptionsSection;
use Alpipego\Resizefly\Admin\OptionsSectionInterface;
use Alpipego\Resizefly\Admin\PageInterface;

final class LicensesSection extends AbstractOptionsSection implements OptionsSectionInterface
{
    private $addons;
    private $pluginPath;

    public function __construct(PageInterface $page, $pluginPath, array $addons)
    {
        $this->optionsGroup = [
            'id'    => 'resizefly_licenses',
            'title' => __('Addon Licenses', 'resizefly'),
        ];
        $this->addons       = $addons;
        $this->pluginPath   = $pluginPath;

        add_filter('resizefly/admin/sections', function () {
            return [
                'resizefly_licenses' => __('Licenses', 'resizefly'),
            ];
        });

        foreach ($this->addons as $addon) {
            ( new LicenseField($page, $this, $pluginPath, $addon) )->run();
        }

        parent::__construct($page, $pluginPath, 'resizefly_licenses');
    }

    public function run()
    {
        parent::run();
    }

    /**
     * Callback function.
     */
    public function callback()
    {
        $this->includeView($this->optionsGroup['id'], [
            'addons' => $this->addons,
        ]);
    }
}
