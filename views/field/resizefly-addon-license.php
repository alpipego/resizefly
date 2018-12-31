<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 13.03.2018
 * Time: 18:48.
 */
?>

    <input
            type="text"
            id="<?= $args['id']; ?>"
            name="<?= $args['id']; ?>"
            class="regular-text"
            placeholder="<?= __('Enter your License Key', 'resizefly'); ?>"
            value="<?= esc_attr($args['license']); ?>"
    />
    <p class="resizefly-license-status resizefly-license-<?= $args['status'] ?: 'invalid'; ?>">
		<?= sprintf(__('Status: %s', 'resizefly'), $args['status_verbose']); ?>
    </p>
<?php
