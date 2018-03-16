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

class RestrictSizesField extends AbstractOption implements OptionInterface {

	/**
	 * RestrictSizesField constructor.
	 *
	 * @param PageInterface $page
	 * @param OptionsSectionInterface $section
	 * @param string $pluginPath
	 */
	public function __construct( PageInterface $page, OptionsSectionInterface $section, $pluginPath ) {
		// set default option
		add_option( 'resizefly_restrict_sizes', true );

		$this->optionsField = [
			'id'    => 'resizefly_restrict_sizes',
			'title' => esc_attr__( 'Restrict Image Sizes', 'resizefly' ),
			'args'  => [ 'class' => 'hide-if-no-js' ],
		];
		parent::__construct( $page, $section, $pluginPath );
	}

	/**
	 * Add a callback to settings field
	 *
	 * @return void
	 */
	public function callback() {
		$this->includeView( $this->optionsField['id'], $this->optionsField );
	}

	/**
	 * Sanitize values added to this settings field
	 *
	 * @param mixed $value value to sanititze
	 *
	 * @return mixed
	 */
	public function sanitize( $value ) {
		return (bool) $value;
	}
}
