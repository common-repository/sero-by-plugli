<?php

namespace Sero\Inc\Admin\Partials\Setup_Wizard;

use Sero\Inc\Helpers\Ajax;
use Sero\Inc\Helpers\Config;
use Sero\Inc\Helpers\Request;

/**
 * trait AjaxActions.
 */
trait AjaxActions {

    public function activate_search_console() {
        $code = Request::post( 'code' );
        $code = $code ? trim( wp_unslash( $code ) ) : false;
        if ( ! $code ) {
            Ajax::error( 'No authentication code found.' );
        }

        $api = $this->get_google_client();
        $response = $api->get_access_token( $code );

        if ( ! $api->is_success() ) {
            Ajax::error($api->get_error());
        }

        Config::set(
            'sero_search_console_options',
            [
                'authorised'    => true,
                'expire'        => time() + $response['expires_in'],
                'access_token'  => $response['access_token'],
                'refresh_token' => $response['refresh_token'],
            ]
        );

        $api->set_token( $response['access_token'] );
        $profiles = $api->get_profiles();
        Config::set( 'sero_search_console_options', [ 'profiles' => $profiles ] );
        
        Ajax::success([
            'profiles' => $profiles,
        ]);
    }

    public function get_search_console_profiles() {
        $profiles = [];
        $data = Config::get('sero_search_console_options');

        if ( ! ($data['authorised'] && $data['access_token'] && $data['refresh_token']) ) {
            return Ajax::error('you are not authorised');
        }

        $api  = $this->get_google_client();
        $api->set_token( $data['access_token'] );

        $profiles = $api->get_profiles();
        Config::set( 'sero_search_console_options', [ 'profiles' => $profiles ] );

        if ( empty( $profiles ) ) {
            Ajax::error('nothing found');
        }

        // todo: order by current site
        $current_site = get_site_url();

        if( in_array( $current_site, $profiles, true ) ) {
            $data = Config::set( 'sero_search_console_options', [ 'selected_profile' => $current_site ] );
        }

        Ajax::success([
            'data' => $data,
            'profiles' => $profiles,
        ]);
    }

    public function set_search_console_profile() {
        $profile = Request::post('sero-profile');
        $data = Config::get('sero_search_console_options');

        if ( !in_array( $profile, $data['profiles'], true ) ) {
            Ajax::error('invalid profile');
        }

        if(get_site_url() != $profile) {
            // Ajax::error('Your selected profile must be the same as current WP site');
        }

        $api = $this->get_google_client();
        $api->set_token( $data['access_token'] );
        $data = Config::set( 'sero_search_console_options', [ 'selected_profile' => $profile ] );

        Ajax::success([
            'data' => $data,
            'site_url' => get_site_url(),
            'profiles' => $data['profiles'],
        ]);
    }

    public function deactivate_search_console() {
        $data = Config::get('sero_search_console_options');
        $this->get_google_client()->revoke_token( $data );

        Config::set(
            'sero_search_console_options',
            [
                'authorised'    => false,
                'profiles'   => [],
            ],
            true
        );

        Ajax::success(['message' => 'done']);
    }
}
