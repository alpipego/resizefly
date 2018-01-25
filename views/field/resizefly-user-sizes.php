<?php
/**
 * Option to add Sizes
 *
 * Created by PhpStorm.
 * User: alpipego
 * Date: 26.06.2017
 * Time: 10:40
 */
?>

    <div class="rzf-user-sizes">
        <div class="rzf-user-sizes-single">
            <label>
                <span class="label"><?= __('Width', 'resizefly'); ?></span>
                <input type="number" step="1" min="0" name="resizefly_user_sizes[clone][width]" value="">px
            </label>
        </div>
        <div class="rzf-user-sizes-single">
            <label>
                <span class="label"><?= __('Height', 'resizefly'); ?></span>
                <input type="number" step="1" min="0" name="resizefly_user_sizes[clone][height]" value="">px
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
            <button type="button" class="button-secondary" id="js-rzf-add-user-size">
                <?= __('Add image size', 'resizefly'); ?>
            </button>
        </div>
    </div>

    <div id="js-rzf-user-sizes-error" class="rzf-user-sizes-error"></div>

    <div id="js-rzf-user-sizes"></div>

<?php if (!empty($args['user_sizes'])) : ?>
    <h3><?= __('User Supplied Sizes', 'resizefly'); ?></h3>
    <table class="rzf-image-sizes widefat">
        <thead>
        <tr>
            <th></th>
            <th><?= __('Width', 'resizefly'); ?></th>
            <th><?= __('Height', 'resizefly'); ?></th>
            <th><?= __('Crop', 'resizefly'); ?></th>
            <th><?= __('Name', 'resizefly'); ?></th>
        </tr>
        </thead>
        <tfoot>
        <tr>
            <th></th>
            <th><?= __('Width', 'resizefly'); ?></th>
            <th><?= __('Height', 'resizefly'); ?></th>
            <th><?= __('Crop', 'resizefly'); ?></th>
            <th><?= __('Name', 'resizefly'); ?></th>
        </tr>
        </tfoot>
        <tbody>
        <?php foreach ($args['user_sizes'] as $size) : ?>
            <tr>
                <td>
                    <button class="js-rzf-delete-user-size button-secondary">
                        <?= __('Delete Image Size', 'resizefly'); ?>
                    </button>
                </td>
                <td>
                    <?= $size['width']; ?>
                    <input type="hidden" name="resizefly_user_sizes[<?= $size['name']; ?>][width]" value="<?= $size['width']; ?>">
                </td>
                <td>
                    <?= $size['height']; ?>
                    <input type="hidden" name="resizefly_user_sizes[<?= $size['name']; ?>][height]" value="<?= $size['height']; ?>">
                </td>
                <td>
                    <?php
                    if ($size['crop']) {
                        if (is_array($size['crop'])) {
                            printf('%s, %s', $size['crop'][0], $size['crop'][1]);
                        } else {
                            echo 'center, center';
                        }
                    }
                    ?>
                    <input type="hidden" name="resizefly_user_sizes[<?= $size['name']; ?>][crop]" value="<?= implode(', ',
                        (array)$size['crop']); ?>">
                </td>
                <td>
                    <?= $size['name']; ?>
                    <input type="hidden" name="resizefly_user_sizes[<?= $size['name']; ?>][name]" value="<?= $size['name']; ?>">
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif;
