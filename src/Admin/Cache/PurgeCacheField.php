<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 26/07/16
 * Time: 14:23
 */

namespace Alpipego\Resizefly\Admin\Cache;

use Alpipego\Resizefly\Admin\AbstractOption;
use Alpipego\Resizefly\Admin\OptionInterface;
use Alpipego\Resizefly\Admin\OptionsSectionInterface;
use Alpipego\Resizefly\Admin\PageInterface;

final class PurgeCacheField extends AbstractOption implements OptionInterface
{
    public function __construct(PageInterface $page, OptionsSectionInterface $section, $pluginPath)
    {
        $this->optionsField = [
            'id'    => 'resizefly_purge_cache',
            'title' => __('Purge Cache', 'resizefly'),
            'args'  => ['class' => 'hide-if-no-js'],
        ];
        $this->localize($page);
        parent::__construct($page, $section, $pluginPath);
    }

    private function localize(PageInterface $page)
    {
        $page->localize([
            'purge_result' => sprintf(
                __('%s file(s) have been removed and %s of disk space has been freed.', 'resizefly'),
                '<span class="resizefly-files"></span>',
                '<span class="resizefly-size"></span>'
            ),
            'purge_empty'  => __('No files were removed because the cache was already empty.', 'resizefly'),
            'purge_id'     => $this->optionsField['id'],
        ]);
    }

    public function callback()
    {
        $path = get_option('resizefly_resized_path', 'resizefly');
        if ( ! empty($path)) {
            $uploadDir = wp_upload_dir(null, false);
            $path      = trailingslashit(trailingslashit($uploadDir['basedir']) . $path);
        }

        $args = array_merge($this->optionsField, ['path' => $path]);
        $this->includeView($this->optionsField['id'], $args);
    }

    public function sanitize($value)
    {
        return $value;
    }

}
