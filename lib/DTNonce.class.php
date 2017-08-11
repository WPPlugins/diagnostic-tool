<?php

if (!class_exists( 'WP_Http' ))
	include_once( ABSPATH . WPINC. '/class-http.php' );

class DTNonce {

	static function checkNonce() {
		if (!isset($_POST['dt-nonce'])) {
			header('Content-Type: application/json');
			$resJSON['result'] = false;
			$resJSON['message'] = 'Nonce Check 1';
			echo json_encode($resJSON);
			die();
		}

		if (!wp_verify_nonce($_POST['dt-nonce'], 'dt-nonce')) {
			header('Content-Type: application/json');
			$resJSON['result'] = false;
			$resJSON['message'] = 'Nonce Check 2';
			echo json_encode($resJSON);
			die();
		}
	}
}
