<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 26/07/16
 * Time: 14:39
 */
?>

<p>
    <?php if ($args['path']) : ?>
        <?= sprintf(__('Remove %s resized images in <code>%s</code>.', 'resizefly'), '<strong id="rzf-cache-text">most</strong>', $args['path']); ?>
        <br/>
        <em><?= __('(Note: This will not remove any images in your default upload folder.)', 'resizefly'); ?></em>
    <?php else : ?>
        <?= __('To configure more options, please add a resize path above.', 'resizefly'); ?>
    <?php endif; ?>
</p>
<p>
    <label for="<?= $args['id']; ?>-smart">
        <input type="checkbox" id="<?= $args['id']; ?>-smart" name="<?= $args['id']; ?>-smart" checked>
        <?= __('Smart purge: Retains often used sizes such as thumbnails', 'resizefly'); ?>
    </label>
</p>

<p id="<?= $args['id']; ?>-result"></p>

<button id="<?= $args['id']; ?>" data-nonce="<?= wp_create_nonce($args['id']); ?>" class="button" <?php disabled($args['path'], false, true); ?> type="button"><?= $args['title']; ?></button>
