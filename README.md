# Resizefly
Resize all your WordPress images on the fly (works with `*.jp(e)g`, `*.png` or `*.gif` files).

## How it works
 Based on otto42's plugin [Dynamic Image Resizer](https://wordpress.org/plugins/dynamic-image-resizer/) this plugin hooks into the `template_redirect` and checks if the requested file is an image. It then resizes the image to the specified size, as appended by WordPress in the format `-[0-9]+x[0-9]+`, e.g. `-300x200`.
  
  If one of the aspects is set to `0` it resizes proportionally to the original image aspect ratio.
  
## Caveats
* If one of the aspects is not specified, the whole plugin has to run to check the original aspect ratio and see if that image exists. Thus making it way slower (than simply serving the image) even if the image already exists
* When a lot of different image sizes are requested (at the same time or in fast succession), this plugin may have a huge impact on performance
