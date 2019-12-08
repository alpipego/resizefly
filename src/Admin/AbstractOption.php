<?php

namespace Alpipego\Resizefly\Admin;

/**
 * Class AbstractOption.
 */
abstract class AbstractOption implements OptionInterface
{
    /**
     * @var array id and title of the field
     */
    protected $optionsField = [
        'id'    => null,
        'title' => null,
    ];

    /**
     * @var string path to views dir
     */
    protected $viewsPath;

    /**
     * @var string id of page to pass to add_settings_field
     */
    protected $optionsPage;

    /**
     * @var string id of field group to pass to add_settings_field
     */
    protected $optionsGroup;

    /**
     * AbstractOption constructor.
     *
     * @param PageInterface           $page       The parent page
     * @param OptionsSectionInterface $section    The containing section
     * @param string                  $pluginPath Plugin base path
     */
    public function __construct(PageInterface $page, OptionsSectionInterface $section, $pluginPath)
    {
        $this->optionsPage  = $page;
        $this->optionsGroup = $section;
        $this->viewsPath    = $pluginPath.'views/';
    }

    /**
     * Add the field to WP Admin.
     */
    public function run()
    {
        add_action('admin_init', [$this, 'addField']);
        add_action('admin_init', [$this, 'registerSetting']);
    }

    /**
     * Register the option.
     */
    public function registerSetting()
    {
        register_setting($this->optionsPage->getId(), $this->optionsField['id'], [$this, 'sanitize']);
    }

    /**
     * Add settings field.
     */
    public function addField()
    {
        add_settings_field(
            $this->optionsField['id'],
            $this->optionsField['title'],
            [$this, 'callback'],
            $this->optionsPage->getId(),
            $this->optionsGroup->getId(),
            ! empty($this->optionsField['args']) ? $this->optionsField['args'] : []
        );
    }

    public function getId()
    {
        return $this->optionsField['id'];
    }

    public function getTitle()
    {
        return $this->optionsField['title'];
    }

    public function getView($name)
    {
        $fileArr = preg_split('/(?=[A-Z-_])/', $name);
        $fileArr = array_map(function (&$value) {
            return trim($value, '-_');
        }, $fileArr);
        $fileArr = array_map('strtolower', $fileArr);

        return $this->viewsPath.'field/'.implode('-', $fileArr).'.php';
    }

    /**
     * Include the view and pass optional variables.
     *
     * @param string $name name for view to use
     * @param array  $args variables to pass to view
     */
    protected function includeView($name, $args = [])
    {
        include $this->getView($name);
    }
}
