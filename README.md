A dynamic attachment resizing plugin for WordPress

# Installation
Tell your server to redirect all request to JPEGs to `image.php`, e.g.

```
location ~* (\.jpg|jpeg)$ {
    rewrite ^/* /wp-content/dynamic-image-resizer/image.php?image=$uri last;
}
```

# Query Args
Append `width`, `height` and `quality` as optional query args to get an image in the specified size. Leave out either `width` or `height` to resize proportionally. If you do not specify `quality` the WordPress default will be used (90% pre WP 4.5; 82% since WP 4.5).

# Dependencies
* WordPress > 3.5.0 (WP_Image_Editor Class)
* php > 5.4
* either GD or ImageMagick installed on the server

```
"require": {
    "php": ">=5.4",
    "composer/installers": "~1.0"
}
```

# Disclaimer
This plugin has been 'tested' (not actually 'tested') with WordPress 4.5, nginx, php-7.0.3

You should not use it on a production server as of now, since your server could easily be DOS attacked by requesting a lot of different sizes.

