<?php

namespace Sero\Inc\Admin\Partials\Settings;

use \Exception;
use Sero\Inc\Helpers\Ajax;
use Sero\Inc\Helpers\Config;
use Sero\Inc\Helpers\Request;
use Sero\Inc\Helpers\Console\Console;
use Sero\Inc\Helpers\Collection\Collection;

trait AjaxActions_Console {
    private $client;

	private function get_client () {
        if(empty($this->client))
            $this->client = new Console();

        return $this->client;
    }

    public function activate_search_console() {
        try { 
            $code = Request::post( 'code' );
            $code = $code ? trim( wp_unslash( $code ) ) : false;
            if ( ! $code ) {
                throw new Exception("No authentication code found.", 1);
            }

            $response = $this->get_client()->activate_code( $code );
            $this->get_client()->set_token(  $response['access_token'] );

            Config::set(
                'sero_search_console_options',
                [
                    'authorised'    => true,
                    'expire'        => time() + $response['expires_in'],
                    'access_token'  => $response['access_token'],
                    'refresh_token' => $response['refresh_token'],
                ]
            );

            Ajax::success(['message' => 'done']);
        } catch (Exception $e) {
            Ajax::error($e->getMessage());
        }
    }

    public function get_search_console_profiles() {
        try { 
            $profiles = $this->get_client()->refresh_profiles();
            $data = Config::get('sero_search_console_options');

            if ( empty( $profiles ) ) {
                throw new Exception("nothing found", 1);
            }

            Ajax::success($profiles);
        } catch (Exception $e) {
            Ajax::error($e->getMessage());
        }
    }

    public function set_search_console_profile() {
        try { 
            $profile = Request::post('profile');
            $data = Config::get('sero_search_console_options');

            if ( !in_array( $profile, $data['profiles'], true ) ) {
                throw new Exception("invalid profile", 1);
            }

            $this->get_client()->set_profile($profile);

            Ajax::success([
                'profile' => $profile,
            ]);
        } catch (Exception $e) {
            Ajax::error($e->getMessage());
        }
    }
}