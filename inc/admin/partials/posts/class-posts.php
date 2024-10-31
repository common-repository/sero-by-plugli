<?php

namespace Sero\Inc\Admin\Partials\Posts;

use Sero\Inc\Admin\Partials\Base;

/**
 * Posts class.
 */
class Posts extends Base {

	use AjaxActions;

    public function admin_init() {
		$this->module = 'posts';

		$this->action( 'wp_ajax_sero_posts_get_query_data', 'sero_posts_get_query_data');
        $this->action( 'wp_ajax_sero_posts_get_query_count', 'sero_posts_get_query_count');
        $this->action( 'wp_ajax_sero_posts_get_query_summary', 'sero_posts_get_query_summary');
    }

    public function register_admin_page($plugin_name, $version){
    	$this->version = $version;
    	$this->plugin_name = $plugin_name;
    	$this->page = $plugin_name. '-posts';


		add_submenu_page(
			$plugin_name, 
			'Posts', 
			'Posts', 
			'manage_options', 
			$this->page,
			array($this, 'display_page')
		);
    }

    public function enqueue_scripts() {
    	parent::enqueue_scripts();

    	wp_localize_script($this->plugin_name, 'categories', get_categories());
    	wp_localize_script($this->plugin_name, 'types', get_post_types([
    		'public' => true
    	]));
    }
}
