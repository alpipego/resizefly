<?php
/**
 * List all registered (and user added) image sizes
 *
 * Created by PhpStorm.
 * User: alpipego
 * Date: 25.06.2017
 * Time: 12:42
 *
 * @var array $args passed view arguments
 */
?>
<table class="widefat rzf-image-sizes" id="rzf-image-sizes">
    <thead>
    <tr>
        <th></th>
        <th><?= __('Active', 'resizefly'); ?></th>
        <th><?= __('Width', 'resizefly'); ?></th>
        <th><?= __('Height', 'resizefly'); ?></th>
        <th><?= __('Crop', 'resizefly'); ?></th>
        <th><?= __('Name', 'resizefly'); ?></th>
    </tr>
    </thead>
    <tfoot>
    <tr>
        <th></th>
        <th><?= __('Active', 'resizefly'); ?></th>
        <th><?= __('Width', 'resizefly'); ?></th>
        <th><?= __('Height', 'resizefly'); ?></th>
        <th><?= __('Crop', 'resizefly'); ?></th>
        <th><?= __('Name', 'resizefly'); ?></th>
    </tr>
    </tfoot>
    <tbody>
    <?php foreach ($args['image_sizes'] as $name => $size) : ?>
        <tr>
            <td>
                <?php
                foreach ($args['out_of_sync'] as $status => $sizes) {
                    if (isset($sizes[$name])) {
                        echo $status;
                    }
                }
                ?>
            </td>
            <td>
                <input type="checkbox" name="resizefly_sizes[<?= $name; ?>][active]" <?php checked($size['active'], 1); ?>>
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
                <?php
                if ($size['crop']) {
                    if (is_array($size['crop'])) {
                        printf('%s, %s', $size['crop'][0], $size['crop'][1]);
                    } else {
                        echo 'center, center';
                    }
                }
                ?>
                <input type="hidden" name="resizefly_sizes[<?= $name; ?>][crop]" value="<?= implode(', ', (array)$size['crop']); ?>">
            </td>
            <td><?= $name; ?></td>
        </tr>
    <?php endforeach; ?>
    <?php foreach ($args['out_of_sync']['missing'] as $name => $size) : ?>
        <tr>
            <td>
                <?php
                foreach ($args['out_of_sync'] as $status => $sizes) {
                    if (isset($sizes[$name])) {
                        echo $status;
                    }
                }
                ?>
            </td>
            <td>
                <input type="checkbox" disabled="disabled" <?php checked($size['active'], 1); ?>>
            </td>
            <td>
                <?= $size['width']; ?>
            </td>
            <td>
                <?= $size['height']; ?>
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
            </td>
            <td><?= $name; ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
