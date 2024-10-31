<?php

namespace Sero\Inc\Admin\Partials\Pages;

use Sero\Inc\Admin\Partials\Base;

/**
 * Queries class.
 */
class Pages extends Base {

	use AjaxActions;

    public function admin_init() {
		$this->module = 'pages';

		$this->action( 'wp_ajax_sero_pages_get_query_data', 'sero_pages_get_query_data');
        $this->action( 'wp_ajax_sero_pages_get_query_count', 'sero_pages_get_query_count');
        $this->action( 'wp_ajax_sero_pages_get_query_summary', 'sero_pages_get_query_summary');
    }

    public function register_admin_page($plugin_name, $version){
    	$this->version = $version;
    	$this->plugin_name = $plugin_name;
    	$this->page = $plugin_name. '-pages';

		add_submenu_page(
			$plugin_name, 
			'Pages', 
			'Pages', 
			'manage_options', 
			$this->page,
			array($this, 'display_page')
		);
    }
}

