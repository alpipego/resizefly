<?php
/**
 * List all registered (and user added) image sizes.
 *
 * Created by PhpStorm.
 * User: alpipego
 * Date: 25.06.2017
 * Time: 12:42
 *
 * @var array passed view arguments
 */
?>
<div id="rzf-image-sizes">
	<?php include __DIR__.'/partials/sizes/legend.php'; ?>
    <table class="widefat rzf-image-sizes">
        <thead>
        <tr>
            <th class="rzf-size-status"></th>
            <th><?= __('Active', 'resizefly'); ?></th>
            <th><?= __('Action', 'resizefly'); ?></th>
            <th><?= __('Width', 'resizefly'); ?></th>
            <th><?= __('Height', 'resizefly'); ?></th>
            <th><?= __('Crop', 'resizefly'); ?></th>
            <th><?= __('Name', 'resizefly'); ?></th>
        </tr>
        </thead>
        <tfoot>
        <tr>
            <th class="rzf-size-status"></th>
            <th><?= __('Active', 'resizefly'); ?></th>
            <th><?= __('Action', 'resizefly'); ?></th>
            <th><?= __('Width', 'resizefly'); ?></th>
            <th><?= __('Height', 'resizefly'); ?></th>
            <th><?= __('Crop', 'resizefly'); ?></th>
            <th><?= __('Name', 'resizefly'); ?></th>
        </tr>
        </tfoot>
        <tbody>
		<?php foreach ($args['image_sizes'] as $name => $size) { ?>
			<?php
            $user   = isset($args['user_sizes'][$name]);
            $status = function () use ($args, $name) {
                foreach ($args['out_of_sync'] as $status => $sizes) {
                    if (isset($sizes[$name])) {
                        return $status;
                    }
                }

                return '';
            };
            ?>
            <tr
                    class="rzf-size <?= empty($status()) ? '' : 'rzf-size-status-'.$status().''; ?>"
                    data-width="<?= $size['width']; ?>"
                    data-height="<?= $size['height']; ?>"
                    data-crop="<?= (bool) $size['crop']; ?>"
                    data-name="<?= $name; ?>"
                    data-active="<?= $size['active'] ? 'on' : ''; ?>"
            >
                <td class="rzf-size-status"></td>
                <td class="rzf-size-active">
                    <input
                            type="checkbox"
                            name="resizefly_sizes[<?= $name; ?>][active]"
						<?php checked($size['active'], 1); ?>
						<?= ($user || 'missing' === $status()) ? 'disabled' : ''; ?>
                    >
					<?php if ($user) { ?>
                        <input type="hidden" name="resizefly_sizes[<?= $name; ?>][active]" value="on">
					<?php } ?>
                </td>
                <td class="rzf-size-action">
					<?php if ($user) { ?>
                        <button
                                type="button"
                                class="js-rzf-user-size-delete button-secondary"
                                data-nonce="<?= wp_create_nonce($args['delete_action']); ?>"
                                data-action="<?= $args['delete_action']; ?>"
                                data-size-name="<?= $name; ?>"
                        >
							<?= _x('Delete Size', 'button text', 'resizefly'); ?>
                        </button>
					<?php } ?>

					<?php if ('missing' === $status()) { ?>
                        <button
                                type="button"
                                class="js-rzf-user-size-readd button-secondary"
                                data-nonce="<?= wp_create_nonce($args['add_action']); ?>"
                                data-action="<?= $args['add_action']; ?>"
                                data-size-name="<?= $name; ?>"
                        >
							<?= _x('Add Size', 'button text', 'resizefly'); ?>
                        </button>
					<?php } ?>
                </td>
                <td class="rzf-size-width">
					<?= $size['width']; ?>

					<?php if ('missing' !== $status()) { ?>
                        <input type="hidden" name="resizefly_sizes[<?= $name; ?>][width]" value="<?= $size['width']; ?>">
					<?php } ?>
                </td>
                <td class="rzf-size-height">
					<?= $size['height']; ?>
					<?php if ('missing' !== $status()) { ?>
                        <input type="hidden" name="resizefly_sizes[<?= $name; ?>][height]" value="<?= $size['height']; ?>">
					<?php } ?>
                </td>
                <td class="rzf-size-crop">
					<?php
                    if ($size['crop']) {
                        if (is_array($size['crop'])) {
                            printf('%s, %s', $size['crop'][0], $size['crop'][1]);
                        } else {
                            echo 'center, center';
                        }
                    }
                    ?>
					<?php if ('missing' !== $status()) { ?>
                        <input
                                type="hidden"
                                name="resizefly_sizes[<?= $name; ?>][crop]"
                                value="<?= implode(', ', (array) $size['crop']); ?>"
                        >
					<?php } ?>
                </td>
                <td class="rzf-size-name"><?= $name; ?></td>
            </tr>
		<?php } ?>
        </tbody>
    </table>

	<?php include __DIR__.'/partials/sizes/add-new-form.php'; ?>

	<?php include __DIR__.'/partials/sizes/row-template.php'; ?>
</div>
