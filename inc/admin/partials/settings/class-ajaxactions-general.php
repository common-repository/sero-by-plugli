<?php

namespace Sero\Inc\Admin\Partials\Settings;

use \Exception;
use Sero\Inc\Helpers\Ajax;
use Sero\Inc\Helpers\Config;
use Sero\Inc\Helpers\Request;

trait AjaxActions_General {
    
    public function reset_general_settings() {
        try { 
            $date_mode = Request::post( 'date_mode', 'compare' );
            $search_type= Request::post( 'search_type', 'web' );
            $device = Request::post( 'device', 'all' );

            Config::set(
                'sero_user_options',
                [
                    'sc_date_mode' => $date_mode, // single || compare
                    'sc_search_type' => $search_type,
                    'sc_device' => $device
                ]
            );

            Ajax::success([
                'sc_date_mode' => $date_mode,
                'sc_search_type' => $search_type,
                'sc_device' => $device
            ]);
        } catch (Exception $e) {
            Ajax::error($e->getMessage());
        }
    }

    public function update_general_settings() {
        try { 
            $date_mode = Request::post( 'date_mode', 'compare' );
            $search_type= Request::post( 'search_type', 'web' );
            $device = Request::post( 'device', 'all' );

            Config::set(
                'sero_user_options',
                [
                    'sc_date_mode' => $date_mode, // single || compare
                    'sc_search_type' => $search_type,
                    'sc_device' => $device
                ]
            );

            Ajax::success([
                'sc_date_mode' => $date_mode,
                'sc_search_type' => $search_type,
                'sc_device' => $device
            ]);
        } catch (Exception $e) {
            Ajax::error($e->getMessage());
        }
    }
}