<?php
/**
 * Filter URL sent to editor to make sure metadata is not corrupted.
 *
 * Created by PhpStorm.
 * User: alpipego
 * Date: 11.02.2018
 * Time: 11:34
 */
add_filter('media_send_to_editor', function ($html, $id, $attachment) {
    if ('full' === $attachment['image-size'] && preg_match('/src="[^"]+?array"/i', $html)) {
        $html = preg_replace('/src="[^"]+?array"/i', 'src="'.wp_get_attachment_url($id).'"', $html);

        // update the metadata while we're at it
        $metaData = wp_get_attachment_metadata($id);
        if (is_array($metaData['sizes']['full']['file'])) {
            $metaData['sizes']['full']['file'] = $metaData['sizes']['full']['file'][0];
            wp_update_attachment_metadata($id, $metaData);
        }
    }

    return $html;
}, 9, 3);
