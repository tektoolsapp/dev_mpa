<?php

namespace App\Myob;


class MyobApi
{
    public function getURL($url, $username=NULL, $password=NULL, $api_key, $un, $pw) {

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

        return $response;

    }

    function postURL($url, $post_data, $username=NULL, $password=NULL, $api_key, $un, $pw) {

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

        return $output;

        curl_close($session);
    }

}