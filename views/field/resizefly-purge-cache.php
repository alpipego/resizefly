<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 26/07/16
 * Time: 14:39
 */
?>

<p>
	<?php
	if ( $args['path'] ) {
		printf( __( 'Remove <strong>all</strong> resized images in <code>%s</code>.', 'resizefly' ), $args['path'] );
		echo '<br /><em>';
		_e( '(Note: This will not remove any images in your default upload folder.)', 'resizefly' );
		echo '</em>';
	} else {
		_e( 'To configure more options, please add a resize path above.', 'resizefly' );
	}
	?>
	<p id="<?= $args['id']; ?>-result"></p>
</p>

<button id="<?= $args['id']; ?>" data-nonce="<?= wp_create_nonce($args['id']); ?>" class="button" <?php disabled( $args['path'], false, true ); ?> type="button"><?= $args['title']; ?></button>
