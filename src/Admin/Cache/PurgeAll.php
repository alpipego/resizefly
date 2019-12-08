<?php

namespace Alpipego\Resizefly\Admin\Cache;

use Alpipego\Resizefly\Admin\OptionInterface;
use Alpipego\Resizefly\Upload\Cache;

class PurgeAll
{
    private $action;
    private $cache;

    public function __construct(OptionInterface $field, Cache $cache)
    {
        $this->action    = $field->getId();
        $this->cache     = $cache;
    }

    public function run()
    {
        add_action("wp_ajax_{$this->action}", [$this, 'ajaxPurge']);
    }

    public function ajaxPurge()
    {
        if (
            check_ajax_referer($this->action)
            && current_user_can(apply_filters('resizefly/delete_attachment_cap', 'delete_posts'))
        ) {
            $freed = $this->cache->purgeAll(filter_var($_REQUEST['smart-purge'], FILTER_VALIDATE_BOOLEAN));
            wp_send_json($freed);
        }

        wp_send_json('fail');
    }
}
