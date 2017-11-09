<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 21/07/16
 * Time: 17:54
 */

namespace Alpipego\Resizefly\Admin\Cache;

use Alpipego\Resizefly\Admin\AbstractOption;
use Alpipego\Resizefly\Admin\OptionInterface;
use Alpipego\Resizefly\Admin\OptionsSectionInterface;
use Alpipego\Resizefly\Admin\PageInterface;
use Alpipego\Resizefly\Upload\UploadsInterface;


/**
 * Class PathField
 * @package Alpipego\Resizefly\Admin
 */
class PathField extends AbstractOption implements OptionInterface
{
    private $uploadsPath;
    private $path = '';
    private $permissions = false;
    private $currentSetting = 'resizefly';

    /**
     * PathField constructor.
     *
     * {@inheritDoc}
     */
    public function __construct(
        PageInterface $page,
        OptionsSectionInterface $section,
        UploadsInterface $uploads,
        $pluginPath
    ) {
        $this->optionsField = [
            'id'    => 'resizefly_resized_path',
            'title' => esc_attr__('Path for resized images', 'resizefly'),
        ];

        add_option($this->optionsField['id'], 'resizefly');

        $this->uploadsPath    = $uploads->getBasePath();
        $this->currentSetting = get_option($this->optionsField['id'], 'resizefly');
        $this->path           = trailingslashit(trailingslashit($this->uploadsPath) . $this->currentSetting);
        $this->checkPath();

        parent::__construct($page, $section, $pluginPath);
    }

    private function checkPath()
    {
        $this->permissions = wp_mkdir_p($this->path);

        if (! $this->permissions) {
            add_settings_error($this->optionsField['id'], 'resizefly-dir-writeable',
                sprintf(__('The provided path (%s) is not writeable! Please fix the permissions in your uploads directory.',
                    'resizefly'), "<code>{$this->path}</code>"));
        }
    }

    /**
     * Include view
     *
     * Check if the provided path is writeable - pass permissions back to view
     */
    public function callback()
    {
        $this->includeView($this->optionsField['id'], array_merge($this->optionsField, [
            'path'        => $this->path,
            'permissions' => $this->permissions,
        ]));
    }

    /**
     * Remove slashes from path fragment only use last fragment if longer
     *
     * @param string $path The user submitted path
     *
     * @return string Path fragment to save
     */
    public function sanitize($path)
    {
        $path = sanitize_text_field($path);

        if (strpos($path, '/') !== false) {
            $pathArr = explode('/', $path);
            $pathArr = array_filter($pathArr, function ($value) {

                return ! empty($value);
            });
            $path    = end($pathArr);
            stripslashes($path);
        }

        if ($path !== $this->currentSetting) {
            if (! $this->renameDir($path)) {
                add_settings_error($this->optionsField['id'], 'resizefly-dir-exists',
                    __('This directory already exists. Please remove the directory manually and try setting it again.',
                        'resizefly'));
                $path = $this->currentSetting;
            }
        }

        return $path;
    }

    /**
     * Rename the cache directory (if it does not yet exists)
     *
     * @param string $path The new cache dir name
     *
     * @return bool
     */
    private function renameDir($path)
    {
        $oldPath = $this->path;
        $newPath = trailingslashit(trailingslashit($this->uploadsPath) . $path);

        if (! is_dir($newPath)) {
            return rename($oldPath, $newPath);
        }

        return false;
    }
}
