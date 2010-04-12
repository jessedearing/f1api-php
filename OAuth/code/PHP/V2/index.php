<?php
require_once 'OAuth\AppConfig.php';
require_once 'OAuth\OAuthClient.php';
/********************Third party Aothentication**********************/
$apiConsumer = new OAuthClient(AppConfig::$base_url, AppConfig::$consumer_key, AppConfig::$consumer_secret);
$data = $apiConsumer->authenticateUser();
/*********************2nd party authentication**************************/
$oauth_token = "";
$token_secret = "";
$username = "";
$password = "";
$apiConsumer = new OAuthClient(AppConfig::$base_url, AppConfig::$consumer_key, AppConfig::$consumer_secret);
// 2nd party consumer skips getting the request token part
// To authenticate the user and get the access token, the consumer posts the credentials to the service provider
$requestURL =  sprintf( "%s%s", $apiConsumer->getBaseUrl(), AppConfig::$accesstoken_path );
// SET the username and password
$requestBody = Util::urlencode_rfc3986(base64_encode( sprintf( "%s %s", $username, $password)));

// This is important. If we dont set this, the post will be sent using Content-Type: application/x-www-form-urlencoded (curl will do this automatically)
// Per OAuth specification, if the Content-Type is application/x-www-form-urlencoded, then all the post parameters also need to be part of the base signature string
// To override this, we need to set Content-type to something other than application/x-www-form-urlencoded
$getContentType = array("Accept: application/json",  "Content-type: application/json");
$requestBody	= $apiConsumer->postRequest($requestURL, $requestBody , $getContentType,  200);
preg_match( "~oauth_token\=([^\&]+)\&oauth_token_secret\=([^\&]+)~i", $requestBody, $tokens );
if( !isset( $tokens[1] ) || !isset( $tokens[2] ) ) {
    print 'Tokens are not set'; // Token are not set
}
$access_token = $tokens[1] ;
$token_secret = $tokens[2] ;
print 'Access Tokens: '.$access_token.', token secret: '.$token_secret;

?>