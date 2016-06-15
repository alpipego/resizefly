=== ResizeFly ===
Contributors: alpipego, otto42
Tags: dynamic images, dynamic, image, png, jpg, gif, photo, media library, on-the-fly, resize
Stable tag: 1.1.5
License: GPLv3
Requires at least: 3.5.0
Tested up to: 4.5.2

Dynamically resize your WordPress images on the fly. Upload your images once and don't worry about missing or new image sizes.

== Description ==
Instead of creating image sizes on upload, this plugin only creates them when requested.

Normally after activating a new theme or plugin that adds new image sizes you will have to use a tool like [Regenerate Thumbnails](https://wordpress.org/plugins/regenerate-thumbnails/) to create the newly registered image sizes. Depending on the size of media library and the power of your server. this might take a while or even take several tries to process all your images; and in the end you don't even know if you'll need all of the resized images.

This plugins takes care of the resizing dynamically and creates the requested size the moment it is first requested &ndash; and only when it is requested.

= Support =
For users: Please use the support forums on [wordpress.org](https://wordpress.org/support/plugin/resizefly)<br>
For developers: Head over to the [Github Repository](https://github.com/alpipego/resizefly/)<br>
For everything else, find me on [twitter](https://twitter.com/alpipego) or on slack

== Installation ==
1. Upload the plugin to your plugins directory (usually `wp-content/plugins`)
2. Make sure you have Pretty Permalinks enabled
3. Activate the plugin

The plugin handles the images from here on. There is no administrative menu.

= Prerequisites =

You'll need at least php 5.4 and either GD or Imagick on your server.

= Uninstalling =

If you want to uninstall the plugin for good, make sure to regenerate your image thumbnails/sizes afterwards using [Regenerate Thumbnails](https://wordpress.org/plugins/regenerate-thumbnails/) or similar.

== Frequently Asked Questions ==

Will be added after there has been anything frequent. Please check [the support threads](https://wordpress.org/support/plugin/resizefly) for the time being.

== Changelog ==

= 1.1.5 =
* Check for php version >= 5.4

= 1.1.0 =
* Introduced filters for addons

