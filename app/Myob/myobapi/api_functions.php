<?php

function monitorURL($job) {
	return $job;	
}

function checkExpiry($time_now, $token_expiry, $api_key, $api_secret, $refresh_token) {
	
		if($site_version != 'local') {
			$oauth = new myob_api_oauth();
			$oauth_tokens = $oauth->refreshAccessToken($api_key, $api_secret, $refresh_token);
			$_SESSION['access_token'] = $oauth_tokens->access_token;		
			$expiry_time = $oauth_tokens->expires_in;
			$token_expiry = strtotime(date('h:i:s A', time()+$expiry_time));
			$_SESSION['token_expiry'] = $token_expiry;
		}
}

function getURL($url, $username=NULL, $password=NULL, $api_key, $un, $pw) {

  $this_comp = $un.':'.$pw;
  
  $cftoken = base64_encode($this_comp);
  
  $headers = array(
        'Authorization: Bearer '.$_SESSION['access_token'],
       	'x-myobapi-cftoken: '.$cftoken,
        'x-myobapi-key: '.$api_key,
		'x-myobapi-version: v2'
    );
	
  $session = curl_init($url);
  curl_setopt($session, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($session, CURLOPT_HEADER, false);
  curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
  
  if($username) {
    curl_setopt($session, CURLOPT_USERPWD, $username . ":" . $password);
  }
  
  $response = curl_exec($session);  
  curl_close($session);

  return($response);  

}

function postURL($url, $post_data, $username=NULL, $password=NULL, $api_key, $un, $pw) {

  //$post_data = urlencode($post_data);
  
  $this_comp = $un.':'.$pw;
  $cftoken = base64_encode($this_comp);
  $headers = array(
        'Authorization: Bearer '.$_SESSION['access_token'],
        'x-myobapi-cftoken: '.$cftoken,
        'x-myobapi-key: '.$api_key,
		'x-myobapi-version: v2',
		'Content-Type: application/json',
		'Content-Length: ' . strlen($post_data)
    );
  
	$session = curl_init();
	
	curl_setopt($session, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($session, CURLOPT_URL, $url);
	curl_setopt($session, CURLOPT_POST, true);
	curl_setopt($session, CURLOPT_POSTFIELDS, $post_data);
	
	if($username) {
     	curl_setopt($session, CURLOPT_USERPWD, $username . ":" . $password);
  	}
	curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
	
	$output = curl_exec($session);
	
	return $output;
	
}

function putURL($url, $jsonString, $username=NULL, $password=NULL, $api_key, $un, $pw) {

  	$this_comp = $un.':'.$pw;
  	$cftoken = base64_encode($this_comp);
	
	$headers = array(
        'Authorization: Bearer '.$_SESSION['access_token'],
        'x-myobapi-cftoken: '.$cftoken,
        'x-myobapi-key: '.$api_key,
		'x-myobapi-version: v2',
		'Content-Type: application/json',
		'Content-Length: ' . strlen($jsonString)
   );
  
	$session = curl_init();
	
	curl_setopt($session, CURLOPT_URL, $url);
	curl_setopt($session, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($session, CURLOPT_VERBOSE, 1);
	curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($session, CURLOPT_CUSTOMREQUEST, "PUT"); 
	curl_setopt($session, CURLOPT_POSTFIELDS,$jsonString);
	curl_setopt($session, CURLOPT_SSL_VERIFYPEER, 0);
	$output = curl_exec($session);
	$sessionapierr = curl_errno($session);
	
	//$output = $jsonString;
	//$output = "bla";
	
	return $output;
	
	curl_close($session);
}

if(!function_exists('http_parse_headers'))
{
    function http_parse_headers($raw_headers)
    {
        $headers = array();
        $key = '';

        foreach(explode("\n", $raw_headers) as $i => $h)
        {
            $h = explode(':', $h, 2);

            if(isset($h[1])) {
                if(!isset($headers[$h[0]])) {
                    $headers[$h[0]] = trim($h[1]);
				} elseif (is_array($headers[$h[0]])) {
                    $headers[$h[0]] = array_merge($headers[$h[0]], array(trim($h[1])));
                } else {
                    $headers[$h[0]] = array_merge(array($headers[$h[0]]), array(trim($h[1])));
				}
                $key = $h[0];
            } else { 
                if(substr($h[0], 0, 1) == "\t") {
                    $headers[$key] .= "\r\n\t".trim($h[0]);
				} elseif (!$key) {
                    $headers[0] = trim($h[0]);trim($h[0]);
				}
            }
        }

        return $headers;
    }
}

function getStringBetween($response,$from,$to)
{
    $sub = substr($response, strpos($response,$from)+strlen($from),strlen($response));
    return substr($sub,0,strpos($sub,$to));
}