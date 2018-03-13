<table class="widefat rzf-size-status-desc">
    <tbody>
    <?php foreach ($args['desc'] as $status => $desc) : ?>
        <tr class="rzf-size-status-<?= $status; ?> <?= empty($args['out_of_sync'][$status]) ? 'hidden' : ''; ?>">
            <td class="rzf-size-status"></td>
            <td><?= $desc; ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
