<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 26.06.2017
 * Time: 10:37
 */

namespace Alpipego\Resizefly\Admin\Sizes;


use Alpipego\Resizefly\Admin\AbstractOption;
use Alpipego\Resizefly\Admin\OptionInterface;
use Alpipego\Resizefly\Admin\OptionsSectionInterface;
use Alpipego\Resizefly\Admin\PageInterface;

class UserSizesField extends AbstractOption implements OptionInterface {

	public function __construct(PageInterface $page, OptionsSectionInterface $section, $pluginPath ) {
		$this->optionsField = [
			'id'    => 'resizefly_user_sizes',
			'title' => esc_attr__( 'Add Image Size', 'resizefly' ),
			'args'  => [ 'class' => 'hide-if-no-js', 'label_for' => 'resizefly_user_sizes' ],
		];

		// set default
		add_option( $this->optionsField['id'], [] );

		parent::__construct( $page, $section, $pluginPath );
	}

	/**
	 * Add a callback to settings field
	 *
	 * @return void
	 */
	public function callback() {
		$args               = $this->optionsField;
		$args['user_sizes'] = get_option( $this->optionsField['id'] );

		$this->includeView( $this->optionsField['id'], $args );
	}

	/**
	 * Sanitize values added to this settings field
	 *
	 * @param array $userSizes
	 *
	 * @return array
	 */
	public function sanitize( $userSizes ) {
		unset( $userSizes['clone'] );
		foreach ( $userSizes as &$size ) {
			$size['width']  = (int) $size['width'];
			$size['height'] = (int) $size['height'];
			$crop           = explode( ', ', $size['crop'] );
            $size['crop']   = (bool) $crop[0];
            if (is_array($crop) && count($crop) === 2) {
                $size['crop'] = array_values($crop);
            }
		}

		return $userSizes;
	}
}
