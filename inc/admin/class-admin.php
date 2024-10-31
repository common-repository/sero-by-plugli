<?php

namespace Sero\Inc\Admin;

use Sero\Inc\Helpers\Ajax;
use Sero\Inc\Helpers\Config;
use Sero\Inc\Helpers\Request;
use Sero\Inc\Helpers\Google_Api;

use Sero\Inc\Admin\Partials\Dashboard\Dashboard;
use Sero\Inc\Admin\Partials\Queries\Queries;
use Sero\Inc\Admin\Partials\Pages\Pages;
use Sero\Inc\Admin\Partials\Posts\Posts;
use Sero\Inc\Admin\Partials\Tests\Tests;
use Sero\Inc\Admin\Partials\Groups\Groups;
use Sero\Inc\Admin\Partials\Settings\Settings;
use Sero\Inc\Admin\Partials\Setup_Wizard\SetupWizard;

define("SERO_DATE_FORMAT", "Y-m-d");
define("SERO_DATE_PLUGIN_URL", plugin_dir_url( __FILE__ ) );
define("SERO_DATE_PLUGIN_PATH", plugin_dir_path( __FILE__ ) );

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @link       http://laxusgee.com
 * @since      1.0.0
 *
 * @author    Sero
 */
class Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * The text domain of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_text_domain    The text domain of this plugin.
	 */
	private $plugin_text_domain;


	/**
	* Loaded pages of admin
	*/
	private $pages;
	private $posts;
	private $queries;
	private $dashboard;
	private $setup_wizard;
	private $tests;
	private $settings;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since       1.0.0
	 * @param       string $plugin_name        The name of this plugin.
	 * @param       string $version            The version of this plugin.
	 * @param       string $plugin_text_domain The text domain of this plugin.
	 */
	public function __construct( $plugin_name, $version, $plugin_text_domain ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->plugin_text_domain = $plugin_text_domain;


		$this->pages = new Pages();
		$this->posts = new Posts();
		$this->queries = new Queries();
		$this->dashboard = new Dashboard();
		$this->setup_wizard = new SetupWizard();
		$this->tests = new Tests();
		$this->groups = new Groups();
		$this->settings = new Settings();
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		if(!$this->is_current_page()) {
			return;
		}

		$dir = plugin_dir_url( __FILE__ ).'css/';
		wp_enqueue_style( 'sero-bootstrap', $dir . 'bootstrap.min.css', array(), $this->version, 'all' );
		wp_enqueue_style( 'sero-fontawesome', $dir. 'fontawesome.min.css', array(), $this->version, 'all' );
		wp_enqueue_style( 'sero-bootstrap-ext', $dir . 'bootstrap-ext.min.css', array(), $this->version, 'all' );
		wp_enqueue_style( 'sero-colors', $dir . 'colors.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_name, $dir . 'sero-admin.css', array(), $this->version, 'all' );


		$build_dir = plugin_dir_url( __FILE__ ).'partials/_build/static/css/';
		wp_enqueue_style( $this->plugin_name.' build-chunck',  $build_dir . 'chunk.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_name.' build-main', $build_dir . 'main.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		if(!$this->is_current_page()) {
			return;
		}

		$dir = plugin_dir_url( __FILE__ ).'js/';
		wp_enqueue_script( $this->plugin_name, $dir . 'sero-admin.js', array( 'jquery' ), $this->version, false );


		$build_dir = plugin_dir_url( __FILE__ ).'partials/_build/static/js/';
		wp_enqueue_script( $this->plugin_name.' build-index',  $build_dir . 'index.js', array(  ), $this->version, true );
		wp_enqueue_script( $this->plugin_name.' build-chunck', $build_dir . 'chunk.js', array(  ), $this->version, true );
		wp_enqueue_script( $this->plugin_name.' build-main', $build_dir . 'main.js', array(  ), $this->version, true );
		// wp_enqueue_script( $this->plugin_name.' build-runtime', $dir . 'build/runtime.js', array(  ), $this->version, false );

		wp_localize_script( $this->plugin_name, 'SERO', [
			'admin_url' => admin_url('admin.php'), 
			'site_url' => get_site_url(),
			'plugin_url' => plugin_dir_url(__FILE__),
		] );


		$this->enqueue_admin_script($this->dashboard);
		$this->enqueue_admin_script($this->setup_wizard);
		$this->enqueue_admin_script($this->queries);
		$this->enqueue_admin_script($this->pages);
		$this->enqueue_admin_script($this->posts);
		$this->enqueue_admin_script($this->tests);
		$this->enqueue_admin_script($this->groups);
		$this->enqueue_admin_script($this->settings);
	}

	public function add_plugin_admin_menu() {

		if (get_option('sero_plugin_do_activation_redirect', false)) {
			if(!Config::one('sero_search_console_options', 'authorised')){
				delete_option('sero_plugin_do_activation_redirect');
		    	exit( wp_redirect(admin_url('admin.php?page=sero-wizard')) );
			} 
		}

		$this->register_admin_page($this->dashboard);
		$this->register_admin_page($this->setup_wizard);
		$this->register_admin_page($this->queries);
		$this->register_admin_page($this->pages);
		$this->register_admin_page($this->posts);
		$this->register_admin_page($this->tests);
		$this->register_admin_page($this->groups);
		$this->register_admin_page($this->settings);
		
	}


    private function is_current_page() {
    	if(isset($_GET['page']) && strpos($_GET['page'], 'sero') !== false) {
    		return true;
    	}

    	return false;
    }

    private function enqueue_admin_script($page) {
		$page->enqueue_scripts();
    }

	private function register_admin_page($page) {
		$page->register_admin_page($this->plugin_name, $this->version);
	}
}
