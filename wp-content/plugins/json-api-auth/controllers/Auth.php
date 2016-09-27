<?php
/*
Controller Name: Auth
Controller Description: Authentication add-on controller for the Wordpress JSON API plugin
Controller Author: Matt Berg, Ali Qureshi
Controller Author Twitter: @parorrey
*/


class JSON_API_Auth_Controller {
	
	private $sucess_code = 200;
    
	public function __construct() {
		global $json_api;		
		
		
		// allow only connection over https. because, well, you care about your passwords and sniffing.
		// turn this sanity-check off if you feel safe inside your localhost or intranet.
		// send an extra POST parameter: insecure=cool
		
		/*
		if (empty($_SERVER['HTTPS']) ||
		    (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'off')) {
			if (empty($_REQUEST['insecure']) || $_REQUEST['insecure'] != 'cool') {
				$json_api->error("I'm sorry Dave. I'm afraid I can't do that. (use _https_ please)");
			}
		}
		*/
	
		$allowed_from_post = array('cookie', 'username', 'password', 'seconds', 'nonce');
		foreach($allowed_from_post as $param) {
			if (isset($_POST[$param])) {
				$json_api->query->$param = $_POST[$param];
			}
		}
		
		
	}
	
	public function validate_auth_cookie() {
		global $json_api;
		if (!$json_api->query->cookie) {
			$json_api->error("you must include a 'cookie' authentication cookie",221);
		}
		$valid = wp_validate_auth_cookie($json_api->query->cookie, 'logged_in') ? true : false;
		return array(
			"valid" => $valid,
			"code"=>$this->sucess_code
			);
	}
	public function generate_auth_cookie() {
		global $json_api;
		if (!$json_api->query->username) {
			$json_api->error("you must include a 'username' var in your request",210);
		}
		if (!$json_api->query->password) {
			$json_api->error("you must include a 'password' var in your request",211);
		}
		if ($json_api->query->seconds) 	$seconds = (int) $json_api->query->seconds;
		else $seconds = 1209600;//14 days
		$user = wp_authenticate($json_api->query->username, $json_api->query->password);
		if (is_wp_error($user)) {
			$json_api->error("invalid username or password", 212);
			remove_action('wp_login_failed', $json_api->query->username);
		}
		$expiration = time() + apply_filters('auth_cookie_expiration', $seconds, $user->ID, true);
		$cookie = wp_generate_auth_cookie($user->ID, $expiration, 'logged_in');
		preg_match('|src="(.+?)"|', get_avatar( $user->ID, 32 ), $avatar);
		
		$user_profile_data = array();
		
		if($user->ID)
		{			
			$user_profile_data = get_userdata( $user->ID );
			$user_profile_data->data->cookie = $cookie;
		}
		
		return array("cookie" => $cookie,"code"=>$this->sucess_code,"cookie_name" => LOGGED_IN_COOKIE,"user" => $user_profile_data->data);
	
		/*
		return array(
			"cookie" => $cookie,
			"cookie_name" => LOGGED_IN_COOKIE,
			"user" => array(
				"id" => $user->ID,
				"cookie" => $cookie,
				"username" => $user->user_login,
				"nicename" => $user->user_nicename,
				"email" => $user->user_email,
				"url" => $user->user_url,
				"registered" => $user->user_registered,
				"displayname" => $user->display_name,
				"firstname" => $user->user_firstname,
				"lastname" => $user->last_name,
				"nickname" => $user->nickname,
				"description" => $user->user_description,
				"capabilities" => $user->wp_capabilities,
				"avatar" => $avatar[1]
			),
		);
		*/
		
	}
	
	
	public function check_login() {
		
		global $current_site;
		
		$get_current_site = network_site_url();
		
		print_r($get_current_site);
		
		
		global $json_api;
		if (!$json_api->query->username) {
			$json_api->error("you must include a 'username' var in your request",210);
		}
		if (!$json_api->query->password) {
			$json_api->error("you must include a 'password' var in your request",211);
		}
		if ($json_api->query->seconds) 	$seconds = (int) $json_api->query->seconds;
		else $seconds = 1209600;//14 days
		$user = wp_authenticate($json_api->query->username, $json_api->query->password);
		
		echo "<br> Username : ".$json_api->query->username;
		echo "<br> Password : ".$json_api->query->password;
		
		
		$userInfo     = get_user_by('login', $json_api->query->username);
		
		if($userInfo && !empty($userInfo->user_login) && $userInfo->user_login === $json_api->query->username)
		{
			echo "Valid username";
			
			if ($userInfo && wp_check_password($json_api->query->password, $userInfo->data->user_pass, $userInfo->ID))
			{
				die("......pass correct......");
			}
			else
			{
				die("......pass incorrect......");
			}			
		}
		else
		{
			$json_api->error("invalid username or password", 212);
		}
		
		print_r($userInfo->user_login);
		die;
		
		
		/*
		if ($userInfo && wp_check_password($password, $userInfo->data->user_pass, $userInfo->ID))
		{
			
		}
		*/
		
		if (is_wp_error($user)) {
			$json_api->error("invalid username or password", 212);
			remove_action('wp_login_failed', $json_api->query->username);
		}
		
		die("......pass......");
		
		$expiration = time() + apply_filters('auth_cookie_expiration', $seconds, $user->ID, true);
		$cookie = wp_generate_auth_cookie($user->ID, $expiration, 'logged_in');
		preg_match('|src="(.+?)"|', get_avatar( $user->ID, 32 ), $avatar);
		
		$user_profile_data = array();
		
		if($user->ID)
		{			
			$user_profile_data = get_userdata( $user->ID );
			$user_profile_data->data->cookie = $cookie;
		}
		
		return array("cookie" => $cookie,"code"=>$this->sucess_code,"cookie_name" => LOGGED_IN_COOKIE,"user" => $user_profile_data->data);
	
		/*
		return array(
			"cookie" => $cookie,
			"cookie_name" => LOGGED_IN_COOKIE,
			"user" => array(
				"id" => $user->ID,
				"cookie" => $cookie,
				"username" => $user->user_login,
				"nicename" => $user->user_nicename,
				"email" => $user->user_email,
				"url" => $user->user_url,
				"registered" => $user->user_registered,
				"displayname" => $user->display_name,
				"firstname" => $user->user_firstname,
				"lastname" => $user->last_name,
				"nickname" => $user->nickname,
				"description" => $user->user_description,
				"capabilities" => $user->wp_capabilities,
				"avatar" => $avatar[1]
			),
		);
		*/
		
	}
	
	public function get_currentuserinfo() {
		global $json_api;
		if (!$json_api->query->cookie) {
			$json_api->error("you must include a 'cookie' authentication cookie",221);
		}
		$user_id = wp_validate_auth_cookie($json_api->query->cookie, 'logged_in');
		if (!$user_id) {
			$json_api->error("invalid authentication cookie",222);
		}
		$user = get_userdata($user_id);
		preg_match('|src="(.+?)"|', get_avatar( $user->ID, 32 ), $avatar);
		return array(
			"user" => array(
				"id" => $user->ID,
				"username" => $user->user_login,
				"nicename" => $user->user_nicename,
				"email" => $user->user_email,
				"url" => $user->user_url,
				"registered" => $user->user_registered,
				"displayname" => $user->display_name,
				"firstname" => $user->user_firstname,
				"lastname" => $user->last_name,
				"nickname" => $user->nickname,
				"description" => $user->user_description,
				"capabilities" => $user->wp_capabilities,
				"avatar" => $avatar[1],
				"code"=>$this->sucess_code
			)
		);
	}
}