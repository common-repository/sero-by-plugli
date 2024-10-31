<?php
namespace Sero\Inc\Helpers;

/**
 * Minimal Google API wrapper.
 *
 * @since      1.0.1
 * @package    Sero
 * @subpackage Sero\admin
 * @author     Gbenga Medunoye <ola_leykan@yahoo.com>

/**
 * Request class.
 */
class Request {

	public static function get( $id, $default = false, $filter = FILTER_DEFAULT, $flag = '' ) {
		return filter_has_var( INPUT_GET, $id ) ? filter_input( INPUT_GET, $id, $filter, $flag ) : $default;
	}

	public static function post( $id, $default = false, $filter = FILTER_DEFAULT, $flag = '' ) {
		return filter_has_var( INPUT_POST, $id ) ? filter_input( INPUT_POST, $id, $filter, $flag ) : $default;
	}

	public static function request( $id, $default = false, $filter = FILTER_DEFAULT, $flag = '' ) {
		if(isset( $_REQUEST[ $id ] )){
			return is_array($default)? 
				filter_var_array( $_REQUEST[ $id ], $filter, $flag ) : filter_var( $_REQUEST[ $id ], $filter, $flag );

		} else {
			return $default;
		}
	}

	public static function server( $id, $default = false, $filter = FILTER_DEFAULT, $flag = '' ) {
		return isset( $_SERVER[ $id ] ) ? filter_var( $_SERVER[ $id ], $filter, $flag ) : $default;
	}

	public static function add_query_arg_raw( ...$args ) {
		return esc_url_raw( add_query_arg( ...$args ) );
	}
}
