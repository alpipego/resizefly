<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 09.02.2018
 * Time: 12:18.
 */
?>
<div class="rzf-user-sizes" id="rzf-user-sizes">
    <h3><?= __('Add Image Size', 'resizefly'); ?></h3>
    <div class="rzf-user-sizes-single">
        <label>
            <span class="label"><?= __('Width', 'resizefly'); ?></span>
            <input
                    type="number"
                    pattern="[0-9]*"
                    step="1"
                    min="0"
                    name="resizefly_user_sizes[clone][width]"
                    value=""
            >px
        </label>
    </div>
    <div class="rzf-user-sizes-single">
        <label>
            <span class="label"><?= __('Height', 'resizefly'); ?></span>
            <input
                    type="number"
                    pattern="[0-9]*"
                    step="1"
                    min="0"
                    name="resizefly_user_sizes[clone][height]"
                    value=""
            >px
        </label>
    </div>
    <div class="rzf-user-sizes-single">
        <label>
            <span class="label"><?= __('Crop', 'resizefly'); ?></span>
            <input type="checkbox" name="" id="js-rzf-toggle-crop">
            <input type="hidden" name="resizefly_user_sizes[clone][crop]">
            <select name="" id="js-rzf-crop-x" disabled>
                <option value="left"><?= __('Left', 'resizefly'); ?></option>
                <option value="center" selected><?= __('Center', 'resizefly'); ?></option>
                <option value="right"><?= __('Right', 'resizefly'); ?></option>
            </select>
            <select name="" id="js-rzf-crop-y" disabled>
                <option value="top"><?= __('Top', 'resizefly'); ?></option>
                <option value="center" selected><?= __('Center', 'resizefly'); ?></option>
                <option value="bottom"><?= __('Bottom', 'resizefly'); ?></option>
            </select>
        </label>
    </div>
    <div class="rzf-user-sizes-single">
        <label>
            <span class="label"><?= __('Name', 'resizefly'); ?></span>
            <input type="text" name="resizefly_user_sizes[clone][name]" value="">
        </label>
    </div>
    <div class="rzf-user-sizes-single">
        <button
                type="button"
                class="button-secondary"
                id="js-rzf-user-size-add"
                data-action="<?= $args['add_action']; ?>"
                data-nonce="<?= wp_create_nonce($args['add_action']); ?>"
        >
			<?= __('Add image size', 'resizefly'); ?>
        </button>
    </div>
</div>

<div id="js-rzf-user-sizes-error" class="rzf-user-sizes-error"></div>
