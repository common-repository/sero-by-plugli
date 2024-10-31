<?php

namespace Sero\Inc\Admin\Partials\Groups;

use Sero\Inc\Admin\Partials\Base;
/**
 * Groups class.
 */
class Groups extends Base {

	use AjaxActions;

    public function admin_init() {
		$this->module = 'groups';

		$this->action( 'wp_ajax_sero_groups_add_group', 'add_group');
		$this->action( 'wp_ajax_sero_groups_get_group', 'get_group');
		$this->action( 'wp_ajax_sero_groups_get_groups', 'get_groups');
		$this->action( 'wp_ajax_sero_groups_delete_group', 'delete_group');
    }

    public function register_admin_page($plugin_name, $version){
    	$this->version = $version;
    	$this->plugin_name = $plugin_name;
    	$this->page = $plugin_name. '-groups';

		add_submenu_page(
			$plugin_name, 
			'Groups', 
			'Groups', 
			'manage_options', 
			$this->page,
			array($this, 'display_page')
		);
    }
}
