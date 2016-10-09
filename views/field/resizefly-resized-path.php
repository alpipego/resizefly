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
	if ($args['permissions']) {
		printf(__('Directory <code>%s</code> <span style="color: green">is writeable</span>', 'resizefly'), $args['path']);
	} else {
		printf(__('Directory <code>%s</code> <span style="color: crimson">is not writeable</span>', 'resizefly'), $args['path']);
	}
	?>
</p>
