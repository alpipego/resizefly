<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 21/07/16
 * Time: 17:42
 */
?>
<div class="wrap">
	<h1>Resizefly Settings</h1>
	<?php settings_errors(); ?>
	<form method="post" action="options.php">
		<?php
		do_settings_sections( $args['page'] );
		settings_fields( 'resizefly' );

		submit_button( __( 'Update Options', 'resizefly' ) );
		?>
	</form>
</div>

