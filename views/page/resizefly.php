<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 21/07/16
 * Time: 17:42
 */

$activeTab = isset( $_GET['tab'] ) ? $_GET['tab'] : current( array_keys( $args['sections'] ) );
?>
<div class="wrap">
    <h1>Resizefly Settings</h1>
	<?php settings_errors(); ?>

	<?php if ( count( $args['sections'] ) > 0 ) : ?>
        <h2 class="nav-tab-wrapper">
			<?php foreach ( $args['sections'] as $section => $title ) : ?>
                <a
                        href="?page=resizefly&tab=<?= $section; ?>"
                        class="nav-tab <?= $activeTab === $section ? 'nav-tab-active' : ''; ?>">
					<?= $title; ?>
                </a>
			<?php endforeach; ?>
        </h2>
	<?php endif; ?>
    <form method="post" action="options.php">
		<?php
		do_settings_sections( $activeTab );

		settings_fields( $activeTab );
		submit_button( __( 'Update Options', 'resizefly' ) );
		?>
    </form>
</div>

