<?php

add_filter('wp_get_attachment_image_src', function ($image, $imageId, $size) {
    if (! $image || is_array($size)) {
        return $image;
    }

    $imageMeta = wp_get_attachment_metadata($imageId);
    if (empty($imageMeta)) {
        return $image;
    }


    // does our custom size even exist in the image meta data?
    // if not, then we will have to create it
    if (empty($imageMeta['sizes'][$size])) {
        $sizes = get_option('resizefly_sizes', []);

        if (empty($sizes[$size])) {
            return $image;
        }

        // fix a bug from all version 1 images
        if (empty($imageMeta['sizes']['full'])) {
            $imageMeta['sizes']['full'] = [
                'file'   => array_slice(explode(DIRECTORY_SEPARATOR, $imageMeta['file']), -1)[0],
                'width'  => $imageMeta['width'],
                'height' => $imageMeta['height'],
            ];
        }

        $file = pathinfo($image[0]);
        if ($size !== 'full') {
            $imageMeta['sizes'][$size] = [
                'file'   => sprintf('%s-%dx%d.%s', $file['filename'], $sizes[$size]['width'], $sizes[$size]['height'],
                    $file['extension']),
                'width'  => $sizes[$size]['width'],
                'height' => $sizes[$size]['height'],
            ];
        }

        $image = [
            $file['dirname'] . '/' . $imageMeta['sizes'][$size]['file'],
            $imageMeta['sizes'][$size]['width'],
            $imageMeta['sizes'][$size]['height'],
            $size !== 'full',
        ];

        wp_update_attachment_metadata($imageId, $imageMeta);
    }

    return $image;
}, 9, 3);
