<?php

namespace Sero\Inc\Helpers\Collection;


class Collection {

	/**
	 * Array of all collections objects.
	 *
	 * @var array
	 */
	protected static $instances = [];

	public static function collect( $array_name, $trim = true ) {
		return new Builder( $array_name, $trim );
	}
}
