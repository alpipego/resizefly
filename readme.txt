=== ResizeFly ===
Contributors: alpipego
Tags: dynamic images, dynamic, image, png, jpg, gif, photo, media library, on-the-fly, resize
Stable tag: 3.2.2
License: MIT
Requires at least: 4.7.0
Requires PHP: 5.5
Tested up to: 5.5

Dynamically resize your WordPress images on the fly. Upload them once and don't worry about missing or new image sizes.

== Description ==
Instead of creating image sizes on upload, this plugin only creates them when requested.

Normally after activating a new theme or plugin that adds new image sizes, you will have to use a tool like [Regenerate Thumbnails](https://wordpress.org/plugins/regenerate-thumbnails/) to create the newly registered image sizes. Depending on the size of the media library and the "power" of your server, this might take a while or even take several tries to process all your images; and in the end, you don't even know if you'll need all of the resized images.

This plugin takes care of the resizing dynamically and creates the requested size the moment it is first requested&mdash;and only when it is requested.

= Support =
For users: Please use the support forums on [wordpress.org](https://wordpress.org/support/plugin/resizefly)<br>
For developers: Head over to the [Github repository](https://github.com/alpipego/resizefly/)<br>
For everything else, find me on [twitter](https://twitter.com/alpipego) or on slack

== Installation ==
1. Upload the plugin to your plugins directory (usually `wp-content/plugins`)
2. Make sure you have Pretty Permalinks enabled
3. Activate the plugin

The plugin handles the images from here on. The resized images are saved in a subfolder inside your uploads directory.

= Prerequisites =

You'll need at least PHP 5.5, WordPress 4.7 and either GD or Imagick on your server.

= Uninstalling =

If you want to uninstall the plugin for good, make sure to regenerate your image thumbnails/sizes afterward using [Regenerate Thumbnails](https://wordpress.org/plugins/regenerate-thumbnails/) or similar.

== Frequently Asked Questions ==

= Why does this not work when using NGINX? =
Often your NGINX is configured in a way to serve the image as a static resource and in case of failure throw a 404 Not Found error.

To work around this, make sure the request is sent to WordPress when the image cannot be found.

Add a directive like:

    location ~* (/[^/]+/)?uploads/(.+\.(png|gif|jpe?g)) {
        try_files $uri $uri/ /index.php?q=$uri&$args;
        expires max;
        log_not_found off;
        access_log off;
    }

If you have one long location directive listing all the static file formats (css, js, zip, etc.), you can also just drop the `try_files $uri $uri/ /index.php?q=$uri&$args;` in there.

= Why is there a `resizefly-duplicate` directory in my uploads folder? =
The plugin stores an optimized duplicate of each image in this folder. The reason for this is, that image resizing puts a strain on your server and uses comparably a lot of resources. To minimize this ResizeFly creates an optimized copy from which the smaller image sizes will be created.

== Changelog ==

= 3.2.1 =
* fix request to square image if sizes are larger than original


see https://github.com/alpipego/resizefly/releases for full changelog
