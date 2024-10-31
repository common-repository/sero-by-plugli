<?php
namespace Sero\Inc\Admin\Partials;
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       laxusgooee@gmail.com
 * @since      1.0.0
 *
 * @package    Sero
 * @subpackage Sero/admin/partials
 */

use Sero\Inc\Helpers\Config;
use Sero\Inc\Admin\Traits\Hooker;

abstract class Base {

	use Hooker;

    protected $component;
    protected $module;

	/**
	 * The Constructor.
	 */
	public function __construct($component = null) {
        $this->component = $component;
        $this->admin_init();
	}

    protected function is_current_page() {
    	return isset($this->page) && $this->page === $_GET['page'];
    }

    public function enqueue_styles() {
    	if(!$this->is_current_page()){
    		return;
    	}
    }

	public function enqueue_scripts() {
    	if(!$this->is_current_page()){
    		return;
    	}

    	wp_localize_script($this->plugin_name, 'APP_MODULE', $this->module );
    	wp_localize_script($this->plugin_name, 'USER_OPTIONS', Config::get('sero_user_options' , []) );
    	wp_localize_script($this->plugin_name, 'SETUP_OPTIONS', []);
    	wp_localize_script($this->plugin_name, 'SC_OPTIONS', []);
    	wp_localize_script($this->plugin_name, 'categories',[]);
    	wp_localize_script($this->plugin_name, 'types', []);
	}

	/**
	 * Display admin page.
	 */
    public function display_page() {
    	echo '<div id="root"></div>';
    }

    /**
	 * Admin initialize.
	 */
	abstract public function admin_init();

	/**
	 * Register admin page.
	 */
	abstract public function register_admin_page($plugin_name, $plugin_version);
}
