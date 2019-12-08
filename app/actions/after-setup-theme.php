<?php
/**
 * Register image sizes added by user in backend
 * included in main plugin file.
 */
add_action('after_setup_theme', function () {
    $userSizes = get_option('resizefly_user_sizes', []);
    if (! empty($userSizes)) {
        foreach ($userSizes as $size) {
            if (! is_array($size['crop']) || 2 !== count($size['crop'])) {
                $size['crop'] = (bool) $size['crop'];
            }
            add_image_size($size['name'], (int) $size['width'], (int) $size['height'], $size['crop']);
        }
    }
});
