<?php
ob_start();
include_once('../includes/setup_pg.php');
$log = Logger::getLogger('TTDbLog');
$log->info("API  MYOBAPI- Visited by: ".$id);
header('Content-Type: text/html; charset=utf-8');
include_once('class.myob_oauth.php');

if($access_level == 'A' || $access_level == 'C') {

	$oauth = new myob_api_oauth();
	
	if(isset($_GET['code'])){
		$access_code = $_GET['code'];
	} else {
		$access_code = "No Code";
	}
	
	$oauth_tokens = $oauth->getAccessToken($api_key, $api_secret, $redirect_url, $access_code, $scope);
	
	//print_r($oauth_tokens);
	
	$expiry_time = $oauth_tokens->expires_in;
	
	$token_expiry = strtotime(date('h:i:s A', time()+$expiry_time));
	$_SESSION['token_expiry'] = $token_expiry;
	
	//echo "EXPIRES: ".$token_expiry;
	
	$_SESSION['access_token'] = $oauth_tokens->access_token;
	$_SESSION['refresh_token'] = $oauth_tokens->refresh_token;
	
	if($_SESSION['accounts_integ_status'] == 'A') {
		header ("Location:  http://" . $_SERVER['HTTP_HOST'] . "/view_jobs.php");
		ob_end_flush();
		exit();
	} else {
		header ("Location:  http://" . $_SERVER['HTTP_HOST'] . "/api_setup.php");
		ob_end_flush();
		exit();
	}
	
} else {
	
	header ("Location:  http://" . $_SERVER['HTTP_HOST'] . "/view_jobs.php");
		ob_end_flush();
		exit();
		
}
	