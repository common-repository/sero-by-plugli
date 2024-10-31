<?php

namespace Sero\Inc\Helpers;



// defined( 'ABSPATH' ) || exit;

/**
 * Ajax
 */
class Ajax {
	public static function action($name){
		return 'wp_ajax_sero_'.$name;
	}

	public static function success($data){
		echo json_encode([
			'data' => $data,
			'success' => true
		]);
		wp_die();
	}

	public static function error($data){
		echo json_encode([
			'error' => $data,
			'success' => false
		]);
		wp_die();
	}
}