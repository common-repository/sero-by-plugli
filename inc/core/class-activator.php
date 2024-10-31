<?php

namespace Sero\Inc\Core;

/**
 * Fired during plugin activation
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @link       http://laxusgee.com
 * @since      1.0.0
 *
 * @author     Sero
 **/
class Activator {

	/**
	 * Short Description.
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {

		$min_php = '5.6.0';

		// Check PHP Version and deactivate & die if it doesn't meet minimum requirements.
		if ( version_compare( PHP_VERSION, $min_php, '<' ) ) {
			deactivate_plugins( plugin_basename( __FILE__ ) );
			wp_die( 'This plugin requires a minmum PHP Version of ' . $min_php );
		}

		self::create_tables();
		self::create_options();

	}


	/**
	 * Set up the database tables.
	 */
	private function create_tables() {
		global $wpdb;

		$collate      = $wpdb->get_charset_collate();
		$table_schema = [
			"CREATE TABLE IF NOT EXISTS {$wpdb->prefix}sero_tests (
				id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				post_id BIGINT(20) NOT NULL,
				note TEXT NULL,
				duration mediumint(6) NOT NULL,
				date DATETIME DEFAULT CURRENT_TIMESTAMP,
				is_active tinyint(1) DEFAULT '1',
				is_cancelled tinyint(1) DEFAULT '0',
				PRIMARY KEY (id)
			) $collate;",
			"CREATE TABLE IF NOT EXISTS {$wpdb->prefix}sero_groups (
				id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				title VARCHAR(255) NOT NULL,
				present_size INT(11) DEFAULT 0,
				past_size INT(11) DEFAULT 0,
				date DATETIME DEFAULT CURRENT_TIMESTAMP,
				type tinyint(1) NOT NULL,
				is_active tinyint(1) DEFAULT '1',
				PRIMARY KEY (id)
			) $collate;",
			"CREATE TABLE IF NOT EXISTS {$wpdb->prefix}sero_groups_coditions (
				id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				group_id BIGINT(20) NOT NULL,
				type VARCHAR(255) NOT NULL,
				operator VARCHAR(255) NOT NULL,
				value VARCHAR(255) NOT NULL,
				csvalue VARCHAR(255) NOT NULL,
				date DATETIME DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY (id)
			) $collate;",
		];

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		foreach ( $table_schema as $table ) {
			dbDelta( $table );
		}
	}

	/**
	 * Create options.
	 */
	private function create_options() {
		if(!get_option('sero_plugin_do_activation_redirect')){
		    add_option('sero_plugin_do_activation_redirect', true);
		}

		if(!get_option('sero_search_console_options')){
		    add_option('sero_search_console_options', [
	        	'expire' => null,
	        	'profiles' => [],
	        	'authorised' => false,
	        	'access_token' => null,
	        	'refresh_token' => null,
	        ]);
		}

		if(!get_option('sero_user_options')){
		    add_option('sero_user_options', [
	        	'sc_date_mode' => 'compare', // single || compare
	        	'sc_search_type' => 'web',
	        	'sc_device' => 'all'
	        ]);
		}
	}

}
