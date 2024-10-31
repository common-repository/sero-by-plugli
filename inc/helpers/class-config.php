<?php
namespace Sero\Inc\Helpers;

/**
 * Minimal Google API wrapper.
 *
 * @since      1.0.1
 * @package    Sero
 * @subpackage Sero\admin
 * @author     Gbenga Medunoye <ola_leykan@yahoo.com>
 **/


use Sero\Inc\Helpers\Database\Database;

/**
 * Config class.
 */
class Config{
	public function exists($name, $site_wide=false){
		return  Database::table("options")->where(['option_name' => $name])->one();
	}

	public static function clear($key) {
		delete_option( $key );
		return false;
	}

	public static function set( $key, $data = [], $new = false ) {
		$saved = get_option( $key, null );

		// does not exists
		if ( is_null( $saved )) {
			add_option($key, $data);
		}

		if($new === false){
			$old_data = is_null($saved)? [] : $saved;
			$data = wp_parse_args( $data, $old_data );
		}
			
		update_option( $key, $data );

		return $data;
	}

	public static function get( $key, $defaults = null ) {
		$saved = get_option( $key, [] );

		if ( !is_null( $defaults ) && count($saved) < 1) {
			add_option( $key, wp_parse_args( $defaults, $saved ) );
		}

		return get_option( $key, $defaults );
	}

	public static function one($key, $value) {
		$data = self::get( $key );
		return $data[$value];
	}

	public static function equal($key, $value) {
		$data = self::get( $key );
		return $data === $value;
	}
}
