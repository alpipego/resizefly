<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 26/07/16
 * Time: 14:39
 */
?>

	<p class="description">
		<?php
		_e( 'Remove all resized images in uploads (regardless whether created by Resizefly or not).', 'resizefly' );

		?>
		<p id="<?= $args['id']; ?>-result"></p>
	</p>

	<button id="<?= $args['id']; ?>" data-nonce="<?= wp_create_nonce($args['id']); ?>" class="button" type="button"><?= $args['title']; ?></button>
