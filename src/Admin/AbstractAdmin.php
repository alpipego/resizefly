<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 08/10/16
 * Time: 13:59
 */

namespace Alpipego\Resizefly\Admin;


class AbstractAdmin {
	protected $localized = [];

	public function localizeScript( $toLocalize ) {
		foreach ( $toLocalize as $key => $value ) {
			$this->localized[ $key ] = $value;
		}

		return $this->localized;
	}
}
