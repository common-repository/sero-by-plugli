<?php

namespace Sero\Inc\Admin\Partials\Settings;

use Sero\Inc\Helpers\Config;
use Sero\Inc\Admin\Partials\Base;

/**
 * Settings class.
 */
class Settings extends Base {

	use AjaxActions_General;
	use AjaxActions_Console;

    public function admin_init() {
		$this->module = 'settings';

		// general
		$this->action( 'wp_ajax_sero_settings_general_reset', 'reset_general_settings');
		$this->action( 'wp_ajax_sero_settings_general_update', 'update_general_settings');

		// search console
		$this->action( 'wp_ajax_sero_settings_activate', 'activate_search_console');
		$this->action( 'wp_ajax_sero_settings_set_profile', 'set_search_console_profile');
		$this->action( 'wp_ajax_sero_settings_get_profiles', 'get_search_console_profiles');
    }

    public function register_admin_page($plugin_name, $version){
    	$this->version = $version;
    	$this->plugin_name = $plugin_name;
    	$this->page = $plugin_name. '-settings';

		add_submenu_page(
			$plugin_name, 
			'Settings', 
			'Settings', 
			'manage_options', 
			$this->page,
			array($this, 'display_page')
		);
    }

    public function enqueue_scripts() {
    	parent::enqueue_scripts();
    	$data = Config::get('sero_search_console_options');

    	wp_localize_script($this->plugin_name, 'SC_OPTIONS', [
    		'profiles' => $data['profiles'],
			'selected_profile' => $data['selected_profile'],
			'auth_url' => $this->get_client()->get_google_client()->get_auth_url(),
			'is_authorised' =>  ($data['authorised'] && $data['access_token'] && $data['refresh_token'])
		]);
    }
}
