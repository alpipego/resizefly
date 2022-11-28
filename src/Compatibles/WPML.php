<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 11.12.18
 * Time: 11:26.
 */

namespace Alpipego\Resizefly\Compatibles;

class WPML
{
    public function __construct()
    {
        add_filter('wpml_get_home_url', function ($wpmlUrl, $originalUrl, $path) {
            add_filter('resizefly/home_url', function ($url) use ($originalUrl, $path) {
                if (empty($path) || '/' === $path) {
                    return $originalUrl;
                }

                return $url;
            });

            return $wpmlUrl;
        }, 10, 3);
    }
}
