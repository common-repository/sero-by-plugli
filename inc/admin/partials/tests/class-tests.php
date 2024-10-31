<?php

namespace Sero\Inc\Admin\Partials\Tests;

use Sero\Inc\Admin\Partials\Base;

/**
 * Tgg(Test groups and goals) class.
 */
class Tests extends Base {

	use AjaxActions;

    public function admin_init() {
		$this->module = 'tests';

		$this->action( 'wp_ajax_sero_tests_add_test', 'add_test');
		$this->action( 'wp_ajax_sero_tests_get_test', 'get_test');
		$this->action( 'wp_ajax_sero_tests_get_tests', 'get_tests');
		$this->action( 'wp_ajax_sero_tests_update_test', 'update_test');
		$this->action( 'wp_ajax_sero_tests_cancel_test', 'cancel_test');

		$this->action( 'wp_ajax_sero_tests_get_post', 'get_post');
		$this->action( 'wp_ajax_sero_tests_get_posts', 'get_posts');
    }

    public function register_admin_page($plugin_name, $version){
    	$this->version = $version;
    	$this->plugin_name = $plugin_name;
    	$this->page = $plugin_name. '-tests';

		add_submenu_page(
			$plugin_name, 
			'Tests', 
			'Tests', 
			'manage_options', 
			$this->page,
			array($this, 'display_page')
		);
    }
}
