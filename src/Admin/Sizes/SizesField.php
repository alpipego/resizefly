<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 25.06.2017
 * Time: 12:24
 */

namespace Alpipego\Resizefly\Admin\Sizes;

use Alpipego\Resizefly\Admin\AbstractOption;
use Alpipego\Resizefly\Admin\OptionInterface;
use Alpipego\Resizefly\Admin\OptionsSectionInterface;
use Alpipego\Resizefly\Admin\PageInterface;

class SizesField extends AbstractOption implements OptionInterface {

	/**
	 * @var string option name for out of sync image sizes
	 */
	const OUTOFSYNC = 'resizefly_sizes_outofsync';
	/**
	 * @var string option name for user sizes
	 */
	const USERSIZES = 'resizefly_user_sizes';
	/**
	 * @var string action name, referenced in form and ajax
	 */
	const ADD_ACTION = 'rzf_user_size_add';
	/**
	 * @var string action name, referenced in form and ajax
	 */
	const DELETE_ACTION = 'rzf_user_size_delete';
	/**
	 * @var array
	 */
	private $registeredSizes = [];
	/**
	 * @var array
	 */
	private $savedSizes = [];
	/**
	 * @var PageInterface
	 */
	private $page;
	/**
	 * @var OptionsSectionInterface
	 */
	private $section;
	/**
	 * @var array
	 */
	private $userSizes;

	/**
	 * RestrictSizesField constructor.
	 *
	 * @param PageInterface $page
	 * @param OptionsSectionInterface $section
	 * @param string $pluginPath
	 */
	public function __construct( PageInterface $page, OptionsSectionInterface $section, $pluginPath ) {
		$this->page         = $page;
		$this->section      = $section;
		$this->optionsField = [
			'id'    => 'resizefly_sizes',
			'title' => esc_attr__( 'Image Sizes', 'resizefly' ),
			'args'  => [ 'class' => 'hide-if-no-js', 'label_for' => 'resizefly_sizes_section' ],
		];
		$this->localize( $page );

		parent::__construct( $page, $section, $pluginPath );
	}

	/**
	 * @param PageInterface $page
	 */
	private function localize( PageInterface $page ) {
		$page->localize( [
			'user_size_errors' => [
				'name'            => _x( 'Please choose a unique name.', 'admin custom image size.', 'resizefly' ),
				'dimensions'      => _x( 'Please add either width or height.', 'admin custom image size.', 'resizefly' ),
				'crop_dimensions' => _x( 'When specifying "crop", please add both width and height.', 'admin custom image size', 'resizefly' ),
			],
			'add_button_text'  => _x( 'Add Size', 'button text', 'resizefly' ),
		] );
	}

	public function run() {
		add_action( 'after_setup_theme', function () {
			// get registered and saved image sizes
			$this->savedSizes      = get_option( $this->optionsField['id'], [] );
			$this->userSizes       = get_option( self::USERSIZES, [] );
			$this->registeredSizes = $this->getRegisteredImageSizes();

			// set defaults
			add_option( $this->optionsField['id'], $this->registeredSizes );
			add_option( self::OUTOFSYNC, [] );
			add_option( self::USERSIZES, [] );

			// if there are more user sizes then registered, delete them
			$this->deleteZombieSizes();
		}, 11 );

		add_action( 'current_screen', function ( \WP_Screen $screen ) {
			// check if saved and registered image sizes are in sync
			if ( $screen->id === $this->page->getId() ) {
				$this->imageSizesSynced();
			}
		} );

		add_action( 'wp_ajax_' . self::ADD_ACTION, [ $this, 'addUserSize' ] );
		add_action( 'wp_ajax_' . self::DELETE_ACTION, [ $this, 'deleteUserSize' ] );

		// see if there are out-of-sync image sizes
		add_action( 'admin_notices', [ $this, 'adminSyncNotice' ] );

		// check for out of sync image sizes after theme switch, theme or plugin updates, (de)activation of plugins
		add_action( 'after_switch_theme', [ $this, 'imageSizesSynced' ] );
		add_action( 'upgrader_process_complete', [ $this, 'imageSizesSynced' ] );
		add_action( 'activated_plugin', [ $this, 'imageSizesSynced' ] );
		add_action( 'deactivated_plugin', [ $this, 'imageSizesSynced' ] );

		parent::run();
	}

	/**
	 * Gets and normalizes built in and registered image sizes
	 *
	 * @return array
	 */
	protected function getRegisteredImageSizes() {
		$intermediate = get_intermediate_image_sizes();
		$additional   = wp_get_additional_image_sizes();
		$sizes        = [];

		foreach ( $intermediate as $size ) {
			if ( ! array_key_exists( $size, $additional ) ) {
				$sizes[ $size ]['width']  = (int) get_option( "{$size}_size_w" );
				$sizes[ $size ]['height'] = (int) get_option( "{$size}_size_h" );
				$sizes[ $size ]['crop']   = get_option( "{$size}_crop" );
			} elseif ( isset( $additional[ $size ] ) ) {
				$sizes[ $size ] = [
					'width'  => (int) $additional[ $size ]['width'],
					'height' => (int) $additional[ $size ]['height'],
					'crop'   => $additional[ $size ]['crop'],
				];
			}

			if ( array_key_exists( $size, $sizes ) ) {
				$sizes[ $size ]['active'] = true;
			}
		}

		return $sizes;
	}

	private function deleteZombieSizes() {
		if ( count( $this->userSizes ) !== count( array_intersect_key( $this->savedSizes, $this->userSizes ) ) ) {
			$this->userSizes = array_diff_key( $this->userSizes, array_diff_key( $this->userSizes, $this->savedSizes ) );
			update_option( self::USERSIZES, $this->userSizes );
		}

		return $this->userSizes;
	}

	/**
	 * Check if the saved an (externally) registered image sizes are in sync
	 */
	public function imageSizesSynced() {
		$savedSizes      = array_map( [ $this, 'normalizeSizes' ], $this->savedSizes );
		$registeredSizes = array_map( [ $this, 'normalizeSizes' ], $this->registeredSizes );

		$new = array_udiff_assoc(
			$registeredSizes,
			$savedSizes,
			[ $this, 'compareSizes' ]
		);

		$missing = array_udiff_assoc(
			$savedSizes,
			$registeredSizes,
			[ $this, 'compareSizes' ]
		);

		$updated = array_intersect_key( $new, $missing );
		$new     = array_diff_key( $new, $updated );
		$missing = array_diff_key( $missing, $updated );

		// remove user sizes from updated
		$updated = array_diff_key( $updated, $this->userSizes );

		update_option( self::OUTOFSYNC, [ 'new' => $new, 'updated' => $updated, 'missing' => $missing ] );
	}

	/**
	 * Add a callback to settings field
	 *
	 * @return void
	 */
	public function callback() {
		$args                  = $this->optionsField;
		$args['image_sizes']   = $this->getImageSizes();
		$args['user_sizes']    = $this->userSizes;
		$args['out_of_sync']   = get_option( self::OUTOFSYNC, [] );
		$args['add_action']    = self::ADD_ACTION;
		$args['delete_action'] = self::DELETE_ACTION;
		$args['desc']          = [
			'new'     => __( 'This image size is not yet saved. If you want to allow images in this size save the form.', 'resizefly' ),
			'updated' => __( 'This image size has been updated since your last save.', 'resizefly' ),
			'missing' => sprintf( __( 'This image size is no longer registered. If you still want to keep it you will have to add it manually.', 'resizefly' ), 'rzf-keep-deleted-size' ),
		];

		$this->includeView( $this->optionsField['id'], $args );
	}

	/**
	 * @return array
	 */
	protected function getImageSizes() {
		$sizes = array_merge( $this->registeredSizes, $this->savedSizes );
		array_walk( $sizes, function ( &$size, $name ) {
			if ( array_key_exists( $name, $this->savedSizes ) ) {
				$size['active'] = $this->savedSizes[ $name ]['active'];
			}
		} );

		return $this->sortImageSizes( $sizes );
	}

	/**
	 * @param array $sizes
	 *
	 * @return mixed
	 */
	protected function sortImageSizes( $sizes ) {
		// order results
		uasort( $sizes, function ( $a, $b ) {
			if ( $a['width'] === $b['width'] ) {
				return $a['height'] - $b['height'];
			}

			return $a['width'] - $b['width'];
		} );

		return $sizes;
	}

	/**
	 * Sanitize values added to this settings field
	 *
	 * @param array $sizes
	 *
	 * @return array
	 * @internal param mixed $value value to sanititze
	 *
	 */
	public function sanitize( $sizes ) {
		unset( $sizes['clone'] );
		// cast types
		foreach ( $sizes as &$size ) {
			$size['width']  = (int) $size['width'];
			$size['height'] = (int) $size['height'];
			$size['active'] = isset( $size['active'] );
			$crop           = explode( ', ', $size['crop'] );
			$size['crop']   = (bool) $crop[0];
			if ( is_array( $crop ) && count( $crop ) === 2 ) {
				$size['crop'] = array_values( $crop );
			}
		}

		return $sizes;
	}

	/**
	 *
	 */
	public function deleteUserSize() {
		check_ajax_referer( self::DELETE_ACTION, 'ajax_nonce' );

		$userSizes = get_option( self::USERSIZES, [] );
		$size      = sanitize_key( $_POST['size'] );

		if ( array_key_exists( $size, $userSizes ) ) {
			unset( $userSizes[ $size ] );
			if ( update_option( self::USERSIZES, $userSizes ) ) {
				wp_send_json_success( 'delete ' . sanitize_key( $_POST['size'] ) );
			}
		}

		wp_send_json_error();
	}

	/**
	 *
	 */
	public function addUserSize() {
		check_ajax_referer( self::ADD_ACTION, 'ajax_nonce' );

		$userSizes = get_option( self::USERSIZES, [] );

		$size   = $this->sanitizeAjax( $_POST['size'] );
		$errors = $this->errorHandling( $size, $userSizes );
		if ( ! empty( $errors ) ) {
			wp_send_json_error( $errors, 400 );
		}
		$userSizes[ $size['name'] ] = $size;
		update_option( self::USERSIZES, $userSizes );

		wp_send_json_success( $size );
	}

	/**
	 * Sanitize values added to this settings field
	 *
	 * @param array $size
	 *
	 * @return array
	 */
	private function sanitizeAjax( array $size ) {
		if ( is_array( $size ) && ! empty( $size ) ) {
			$size['width']  = (int) $size['width'];
			$size['height'] = (int) $size['height'];
			$crop           = explode( ', ', $size['crop'] );
			$size['crop']   = (bool) $crop[0];
			if ( is_array( $crop ) && count( $crop ) === 2 ) {
				$size['crop'] = array_values( $crop );
			}
			$size['name'] = sanitize_key( $size['name'] );
		}

		return $size;
	}

	/**
	 * @param array $size
	 * @param array $userSizes
	 *
	 * @return array
	 */
	private function errorHandling( array $size, array $userSizes ) {
		$errors = [];
		if ( in_array( $size['name'], array_keys( array_merge( $userSizes, $this->registeredSizes ) ) ) ) {
			$errors[] = 'name';
		}

		if ( is_array( $size['crop'] ) && ( $size['width'] === 0 || $size['height'] === 0 ) ) {
			$errors[] = 'crop_dimensions';
		}

		if ( ! array_key_exists( 'crop_dimensions', $errors ) ) {
			if ( $size['width'] === 0 && $size['height'] === 0 ) {
				$errors[] = 'dimensions';
			}
		}

		return $errors;
	}

	/**
	 * Outputs the admin notice
	 */
	public function adminSyncNotice() {
		if (
			empty( array_filter( get_option( self::OUTOFSYNC, [] ) ) )
			|| ! (bool) get_option( 'resizefly_restrict_sizes', false )
		) {
			return;
		}
		?>
        <div class="notice notice-warning">
            <p>
				<?=
				sprintf(
					wp_kses(
						__(
							'The registered and saved image sizes for ResizeFly are out of sync. <a href="%s"><button type="button">Please review them here.</button></a>',
							'resizefly'
						),
						[ 'a' => [ 'href' => [] ], 'button' => [ 'type' => [ 'button' ] ] ]
					),
					esc_url( menu_page_url( $this->page->getSlug(), false ) . '#rzf-image-sizes' )
				);
				?>
            </p>
        </div>
		<?php
	}

	/**
	 * @param array $a
	 * @param array $b
	 *
	 * @return int
	 */
	private function compareSizes( $a, $b ) {
		ksort( $a );
		ksort( $b );

		return $a === $b ? 0 : 1;
	}

	/**
	 * @param array $value normalize input array
	 *
	 * @return array
	 */
	private function normalizeSizes( $value ) {
		if ( empty( $value ) ) {
			return $value;
		}
		unset( $value['active'] );
		if ( ! is_array( $value['crop'] ) ) {
			$value['crop'] = (bool) $value['crop'];
		}

		return $value;
	}
}
