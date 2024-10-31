<?php

namespace Sero\Inc\Admin\Partials\Queries;

use Sero\Inc\Admin\Partials\Base;

/**
 * Queries class.
 */
class Queries extends Base {

	use AjaxActions;

    public function admin_init() {
		$this->module = 'queries';

		$this->action( 'wp_ajax_sero_queries_get_query_data', 'sero_queries_get_query_data');
        $this->action( 'wp_ajax_sero_queries_get_query_count', 'sero_queries_get_query_count');
        $this->action( 'wp_ajax_sero_queries_get_query_summary', 'sero_queries_get_query_summary');
    }

    public function register_admin_page($plugin_name, $version){
    	$this->version = $version;
    	$this->plugin_name = $plugin_name;
    	$this->page = $plugin_name. '-queries';

		add_submenu_page(
			$plugin_name, 
			'Queries', 
			'Queries', 
			'manage_options', 
			$this->page,
			array($this, 'display_page')
		);
    }
}
