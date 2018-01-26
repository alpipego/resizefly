<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 21/07/16
 * Time: 17:36
 */

namespace Alpipego\Resizefly\Admin;

/**
 * Class OptionsPage
 * @package Alpipego\Resizefly\Admin
 */
final class OptionsPage extends AbstractPage implements PageInterface {
	/**
	 * @var string $page id of this settings page
	 */
	const PAGE = 'media_page_resizefly';

	const SLUG = 'resizefly';

	/**
	 * @var string $viewsPath path to views dir
	 */
	protected $viewsPath;
    private $pluginUrl;
    private $pluginPath;
    private $slug;

    /**
	 * OptionsPage constructor.
	 *
	 * @param string $pluginPath plugin base path
	 */
	function __construct( $pluginPath, $pluginUrl ) {
	    $this->pluginPath = $pluginPath;
		$this->viewsPath = $pluginPath . 'views/';
		$this->pluginUrl = $pluginUrl;
	}

	public function getSlug() {
	    return self::SLUG;
    }

	/**
	 * Add the page to admin menu action
	 */
	public function run() {
		add_action( 'admin_menu', [ $this, 'addPage' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueueAssets' ] );
	}

	/**
	 * Wrapper for add_media_section
	 *
	 * Add the page in media section
	 */
	function addPage() {
		add_media_page(
			'Resizefly Settings',
			'Resizefly',
			'manage_options',
			self::SLUG,
			[
				$this,
				'callback'
			]
		);
	}

	/**
	 * Include the view
	 */
	function callback() {
		$args = [
			'page' => self::PAGE,
		];

		include $this->viewsPath . 'page/resizefly.php';
	}

    public function enqueueAssets( $page ) {
        if ( $page === self::PAGE ) {
            wp_enqueue_script( 'resizefly-admin', $this->pluginUrl . 'js/resizefly-admin.min.js', [ 'jquery' ], '1.0.0', true );
            wp_add_inline_style( 'wp-admin', file_get_contents( $this->pluginPath . '/css/resizefly-admin.css' ) );

            wp_localize_script( 'resizefly-admin', 'resizefly', $this->localized );
        }
    }

    public function localize(array $localizeArray) {
	    return $this->localized = array_merge($this->localized, $localizeArray);
    }

    public function getId()
    {
        return self::PAGE;
    }
}
