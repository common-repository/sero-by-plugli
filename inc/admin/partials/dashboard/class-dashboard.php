<?php

namespace Sero\Inc\Admin\Partials\Dashboard;

use Sero\Inc\Admin\Partials\Base;

/**
 * Dashboard class.
 */
class Dashboard extends Base {

    public function admin_init() {
        $this->module = 'dashboard';
    }

    public function register_admin_page($plugin_name, $version){
        $this->version = $version;
        $this->plugin_name = $plugin_name;
        $this->page = $plugin_name;

    	add_menu_page( 
			$plugin_name,
			'Sero',
			'manage_options', 
			$plugin_name,
            '',
            'data:image/png;base64,iV
            BORw0KGgoAAAANSUhEUgAAAB
            AAAAAQCAYAAAAf8/9hAAAAr0
            lEQVQ4T2NkoBAwkqt//uJVHv
            +Z/puQZcCCJSt7GBgZi/8x/K
            8lyYCFS1c6/vvP2M/IyKAPcj
            lJBsxfurqVkeF/EIqXGRnPkO
            iC1Wv/Ixvy/385VgPmrFolxP
            Kb4QCybX9YGRxYfzPOJsWAt2
            gGCGM1YN7SlTVMjIzhcMX/Ga
            /8Yf2fzfKbgQQDGBibEbYxrh
            g1YEDCgJHJGRGNDC8gscC4Cj
            Ud/A+DpgMZhNr/awHD6bIes2
            /oKwAAAABJRU5ErkJggg==',
            65
        );	

        add_submenu_page(
            $plugin_name, 
            'Dashboard', 
            'Dashboard', 
            'manage_options', 
            $plugin_name,
            array($this, 'display_page')
        );
    }
}
