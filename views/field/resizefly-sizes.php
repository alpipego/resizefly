<?php
/**
 * List all registered (and user added) image sizes
 *
 * Created by PhpStorm.
 * User: alpipego
 * Date: 25.06.2017
 * Time: 12:42
 */
?>
<table class="widefat rzf-image-sizes">
    <thead>
    <tr>
        <th><?= __( 'Active', 'resizefly' ); ?></th>
        <th><?= __( 'Width', 'resizefly' ); ?></th>
        <th><?= __( 'Height', 'resizefly' ); ?></th>
        <th><?= __( 'Crop', 'resizefly' ); ?></th>
        <th><?= __( 'Name', 'resizefly' ); ?></th>
    </tr>
    </thead>
    <tfoot>
    <tr>
        <th><?= __( 'Active', 'resizefly' ); ?></th>
        <th><?= __( 'Width', 'resizefly' ); ?></th>
        <th><?= __( 'Height', 'resizefly' ); ?></th>
        <th><?= __( 'Crop', 'resizefly' ); ?></th>
        <th><?= __( 'Name', 'resizefly' ); ?></th>
    </tr>
    </tfoot>
    <tbody>
	<?php
	foreach ( $args['image_sizes'] as $name => $size ) :
		?>
        <tr>
            <td>
                <input type="checkbox" name="resizefly_sizes[<?= $name; ?>][active]" <?php checked( $size['active'], 1 ); ?>>
            </td>
            <td>
				<?= $size['width']; ?>
                <input type="hidden" name="resizefly_sizes[<?= $name; ?>][width]" value="<?= $size['width']; ?>">
            </td>
            <td>
				<?= $size['height']; ?>
                <input type="hidden" name="resizefly_sizes[<?= $name; ?>][height]" value="<?= $size['height']; ?>">
            </td>
            <td>
				<?= $size['crop'] ? __( 'true', 'resizefly' ) : __( 'false', 'resizefly' ); ?>
                <input type="hidden" name="resizefly_sizes[<?= $name; ?>][crop]" value="<?= $size['crop']; ?>">
            </td>
            <td><?= $name; ?></td>
        </tr>
		<?php
	endforeach;
	?>
    </tbody>
</table>
