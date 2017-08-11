<?php

if (!class_exists( 'WP_Http' ))
	include_once( ABSPATH . WPINC. '/class-http.php' );

class DTOutboundConnection {

    public function __construct() {
		add_action('http_api_debug', array( $this,'DebugOutbound'), 10, 5);
    }

	public function getTransport() {
		$wpHttp = new WP_Http();
		return $wpHttp->_get_first_available_transport(array());
	}

	public function DebugOutbound($response, $type, $class, $args, $url) {
		global $gDTSettingsVals;

		if ($gDTSettingsVals->disable_http_filter == true) {
			return;
		} 
		$connections = get_option(DTHTTPFILTERLOG);
		if (count($connections) > 18) {
			array_shift($connections);	
		}

		$date = new DateTime();
		$e = $response;
		//var_dump($e);
		if (is_wp_error($e)) {
			$error = $e->get_error_message();
		} else {
			$error = 'success';
		}

		/* CVE-2014-4183 */
		$safeUrl=$url;
		$safeUrl=esc_url($url, array('http', 'https'));
		if ($safeUrl == '') {
			return;
		}

		$connections[] = array(var_export($safeUrl, true), $date->format('Y-m-d H:i:s'), $error);
		update_option(DTHTTPFILTERLOG, $connections);
	}

    public function HTTPCheck() {

		DTNonce::checkNonce();

		header('Content-Type: application/json');
		$url = esc_url($_POST['url'], array('http', 'https'));
		//$request = new WP_Http;
		$result = wp_remote_request($url, array ('timeout' => 20)); //$request->request($url);
		$resJSON=array();
		if (is_wp_error($result)) {
			$resJSON['result'] = false;
			$resJSON['message'] = $result->get_error_messages();
		} else { 
			$resJSON['result'] = true;
			$resJSON['message'] = 'Success';
		}

		echo json_encode($resJSON);
		die();
    }

    public function MailCheck() {

		DTNonce::checkNonce();

		header('Content-Type: application/json');
		$email = $_POST['email'];
	
		if (!is_email($email)) {
			echo json_encode(array('result'=>false, 'message'=>'Invalid Email address'));
			die();
		}

		$testType = intval($_POST['type']);
		$server = (strtolower(substr(php_uname('s'), 0, 3)) === 'win') ? ini_get('SMTP') .':'. ini_get('smtp_port') : ini_get('sendmail_path');

		if (!preg_match('/^[12]$/', $testType)) {
			echo json_encode(array('result'=>false, 'message'=>'Type type invalid'));
			die();
		}

		if (!preg_match('/^[a-zA-Z0-9@\.\-\_]*$/', $email)) {
			echo json_encode(array('result'=>false, 'message'=>'Invalid Email Address'));
			die();
		}

		$subject='Diagnostic Tool - Mail Test - ';
		$body="\n\nTest successful\n\nhttp://wordpress.org/support/plugin/diagnostic-tool ";

		$res=array();
		$res['message'] = 'No debug available';
		if ($testType == 1) {
			$subject = $subject.' PHP builtin';
			$body='The built in PHP mail() function is working correctly.'.$body;
			$mailRes = mail($email, $subject, $body);
		} else {
			$subject = $subject.' Wordpress function';
			$body='The WordPress wp_mail() function is working correctly.'.$body;
			$mailRes = wp_mail($email, $subject, $body);
		}

		if (!$mailRes) {
			$res['message'] = 'Server settings: '.$server;
		} else {
			$res['message'] = 'Success: Pleace check your inbox for &quot;'.$subject.'&quot;';
		}

		$res['result'] = $mailRes;

		echo json_encode($res);
		die();
    }
}
