<?php

namespace Sero\Inc\Helpers\Console;


use Sero\Inc\Helpers\Config;
use Sero\Inc\Helpers\Google_Api;

/**
 * SearchConsole class.
 */
class Console {

    public $api;

    protected $limit;
    protected $offset;
    protected $filters;
    protected $dimensions;
    protected $search_type;
    protected $start_date, $end_date;

    use Console_Builder;

    public function __construct() {
        // reset builder;
        $this->reset();
    }

    public function get_google_client() {
        if ( ! $this->api instanceof Google_Api ) {
            $this->api = new Google_Api;
        }
        return $this->api;
    }

    public function activate_code($code) {
        $api = $this->get_google_client();
        $response = $api->get_access_token( $code );

        if ( ! $api->is_success() ) {
            throw new Exception($api->get_error(), 1);
        }

        return $response;
    }

    public function is_autorised() {
        $data = Config::get('sero_search_console_options');
        return ($data['authorised'] && $data['access_token'] && $data['refresh_token']);
    }

    public function is_expired() {
        $data = Config::one('sero_search_console_options', 'expire');
        return (time() >= $data);
    }

    public function get_profile() {
        return Config::one('sero_search_console_options', 'selected_profile');
    }

    public function set_profile($profile) {
       return Config::set( 'sero_search_console_options', [ 'selected_profile' => $profile ] );
    }

    public function refresh_profiles() {
        $this->refresh_token();
        
        $api = $this->get_google_client();
        return $api->get_profiles();
    }

    public function get_token() {
        return Config::one('sero_search_console_options', 'access_token');
    }

    public function set_token($token) {
        $api = $this->get_google_client();
        $api->set_token( $token );
    }

    public function refresh_token() {
        $api = $this->get_google_client();
        $data = Config::get('sero_search_console_options');

        if(!$this->is_expired()){
            $api->set_token($data['access_token']);
            return true;
        }
        
        $response = $api->refresh_token( $data['refresh_token'] );
        if ( ! $api->is_success() ) {
            throw new \Exception($api->get_error());
        }

        $data = Config::set('sero_search_console_options',[
            'expire'        => time() + $response['expires_in'],
            'access_token'  => $response['access_token']
        ]);

        $api->set_token($data['access_token']);

        return true;
    }

    public function get_query_data($start_date, $end_date, $limit, $offset, $dimensions, $search_type, $filters) {
        $this->refresh_token();

        $api = $this->get_google_client();
        $profile = $this->get_profile();

        $response = $api->get_queries(
            urlencode ($profile), 
            $start_date, 
            $end_date,  
            $limit,
            $offset,
            $dimensions, 
            $search_type,
            $filters
        );

        if (!$api->is_success() ) {
            throw new \Exception($api->get_error());
        }

        return $response;
    }
}
