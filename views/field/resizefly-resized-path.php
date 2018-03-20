<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 22/07/16
 * Time: 06:58
 */
?>

<input type="text" name="<?= $args['id']; ?>" id="<?= $args['id']; ?>" value="<?= get_option( $args['id'], 'resizefly' ); ?>" required>
<p>
	<?php
	if ( $args['permissions'] ) {
		printf( __( 'Directory %s <span class="resizefly-ok">is writable</span>', 'resizefly' ), "<code>{$args['path']}</code>" );
	} else {
		printf( __( 'Directory %s <span class="resizefly-error">is not writable</span>', 'resizefly' ), "<code>{$args['path']}</code>" );
	}
	?>
</p>
