<?php

namespace Sero\Inc\Admin\Partials\Setup_Wizard;

use Sero\Inc\Helpers\Config;
use Sero\Inc\Helpers\Request;
use Sero\Inc\Helpers\Google_Api;
use Sero\Inc\Admin\Partials\Base;

/**
 * Setupwizard class.
 */
class SetupWizard extends Base {

	use AjaxActions;
    public $api;

    public function __construct() {
        parent::__construct();
    }

    public function admin_init() {
    	$this->module = 'setup-wizard';
        $this->action( 'wp_ajax_sero_activate_search_console', 'activate_search_console');
		$this->action( 'wp_ajax_sero_deactivate_search_console', 'deactivate_search_console');
		$this->action( 'wp_ajax_sero_set_search_console_profile', 'set_search_console_profile');
		$this->action( 'wp_ajax_sero_get_search_console_profiles', 'get_search_console_profiles');
    }

    public function register_admin_page($plugin_name, $version){
    	$this->version = $version;
    	$this->plugin_name = $plugin_name;
    	$this->page = $plugin_name.'-wizard';

		add_submenu_page(
			null, 
			$plugin_name.'-wizard', 
			$plugin_name.'-wizard', 
			'manage_options', 
			$this->page, 
			array($this, 'display_page')
		);
    }

    public function get_google_client() {
        if ( ! $this->api instanceof Google_Api ) {
            $this->api = new Google_Api;
        }

        return $this->api;
    }

    public function enqueue_scripts() {
    	parent::enqueue_scripts();
    	global $wpdb;
		
		$memory = rtrim(WP_MEMORY_LIMIT, "M");
		$data = Config::get('sero_search_console_options');
		$res = $wpdb->get_var("SELECT COUNT(1) FROM information_schema.tables WHERE table_schema='{$wpdb->dbname}' AND table_name='{$wpdb->prefix}sero_tests';");

    	wp_localize_script($this->plugin_name, 'SETUP_OPTIONS', [
    		'data' => $data,
    		'user_options' => Config::get('sero_user_options' , []),
    		'step' => empty(Request::get('step'))? 1: Request::get('step'),
			'auth_url' => $this->get_google_client()->get_auth_url(),
			'profiles' => $data['profiles'], 
			'is_allowed_db' => ($res >= 1),
			'is_allowed_memory' => ($memory >= 16),
			'is_authorised' =>  ($data['authorised'] && $data['access_token'] && $data['refresh_token'])
		]);
    }
}
