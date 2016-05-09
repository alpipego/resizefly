<?php

use Alpipego\DynamicImage\ImageHandler;

$defaultQuality = 82;

http_response_code(200);
header('Pragma: public');
header('Cache-Control: max-age=86400, public');
header('Expires: '. gmdate('D, d M Y H:i:s \G\M\T', time() + 86400));

if (isset($_GET) && isset($_GET['width']) && isset($_GET['height'])) {
	$quality = isset($_GET['quality']) ? (int) $_GET['quality'] : $defaultQuality;
	$pathinfo = pathinfo($_GET['image']);
	$file = sprintf('%s/%s-%d-%d-%d.%s', $_SERVER['DOCUMENT_ROOT'] . $pathinfo['dirname'], $pathinfo['filename'], (int) $_GET['width'], (int) $_GET['height'], $quality, $pathinfo['extension']);

	if (file_exists($file)) {
		header('Content-Type:' . mime_content_type($file));
		readfile($file);
		exit;
	}
}

$index = explode('require', file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/index.php'));
preg_match('%[\'"](.+?)[\'"]%', end($index), $wpBlogHeaderArr);
$wpBlogHeader = $_SERVER['DOCUMENT_ROOT'] . end($wpBlogHeaderArr);

if (strpos($wpBlogHeader, 'wp-blog-header')) {
	define( 'WP_USE_THEMES', false );
	require_once $wpBlogHeader;
}

$imageHandler = new ImageHandler($_GET['image']);
$imageHandler->setImageData(wp_upload_dir(), get_bloginfo('url'));
$imageEditor = wp_get_image_editor($imageHandler->image['path']);

if (is_wp_error($imageEditor)) {
	add_action('query_vars', function($vars) {
		$vars[] = '404';

		return $vars;
	});

	status_header(404);
	include_once get_404_template();

	return;
}

$imageHandler->setImageEditor($imageEditor);
$img = $imageHandler->handleImage();

$output = wp_get_image_editor($img['path']);

$output->stream();
