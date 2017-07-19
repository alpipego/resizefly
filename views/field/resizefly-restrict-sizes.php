<?php
/**
 * Checkbox whether or not to restrict image size creation
 *
 * Created by PhpStorm.
 * User: alpipego
 * Date: 25.06.2017
 * Time: 12:28
 */
?>

<input type="checkbox" name="<?= $args['id']; ?>" id="<?= $args['id']; ?>" <?php checked( get_option( $args['id'], 'checked' ), 1 ); ?>>
<p>

</p>
