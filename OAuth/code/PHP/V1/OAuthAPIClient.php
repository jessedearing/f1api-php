<?php
require_once 'RequestSigner.php';
require_once 'AppConfig.php';
require_once 'Util.php';


/**
 * Client library to access F1 API
 *
 * @author jsingh
 */
class OAuthAPIClient {

    private $church_code = null;
    private $consumerKey = null;
    private $consumerSecret = null;

    // This variable is used to store Request Token or the Access token
    private $requestToken; // oauth_token
    private $tokenSecret = "";  // oauth_token_secret

    // The Base URL for the service provider
    private $baseUrl = null;
    private $requesttoken_path = null;
    private $accesstoken_path = null;
    private $auth_path = null;
    // The URL to redirect to after succefull authentication by the Service Provider
    private $callbackUrl = null;
    // Connection to the Host
    private $connection;

    // var $lineBreak = "\r\n";
    var $lineBreak = "<br/>";

    public function __construct($baseUrl, $churchCode, $consumerKey, $consumerSecret) {
        $this->consumerKey = $consumerKey;
        $this->consumerSecret = $consumerSecret;
        $this->baseUrl = str_replace("{church_code}", $churchCode, $baseUrl);

        $this->init_curl();
    }

    /*
     * Initialize the libCurl library functions
     */
    private function init_curl() {
        # Create a new cURL connection
        $this->connection	= curl_init();

        # Prepare the cURL connection

        curl_setopt( $this->connection, CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $this->connection, CURLOPT_POST, true );
        curl_setopt( $this->connection, CURLOPT_UPLOAD, false );
        // The CURLOPT_HEADER option sets whether or not the server response header should be returned
        curl_setopt( $this->connection, CURLINFO_HEADER, false );
        // track request information. it allows the user to retrieve the request sent
        // by cURL to the server. This is very handy and necessary when trying to analyze the full content
        // of the client to server communication. You use
        // curl_getinfo($ch, CURLINFO_HEADER_OUT) to retrieve the request as a string
        curl_setopt( $this->connection, CURLINFO_HEADER_OUT, true );
        curl_setopt( $this->connection, CURLOPT_SSL_VERIFYPEER, false );
    }

    /*
     * Initialize the Access token and token secret. Make this call to set the
     * Access token and token secret before accessing any protected resources
     */
    public function init_AccessToken($access_token, $token_secret) {
        $this->requestToken = $access_token;
        $this->tokenSecret = $token_secret;
    }

    /************* START- Path Setters ********************/
    public function setRequestTokenPath( $requestPath ) {
        # Add the root slash if it's missing.
        if( substr( $requestPath, 0, 1 ) != "/" ) {
            $requestPath	= "/" . $requestPath;
        }

        $this->requesttoken_path	= $requestPath;
    }

    public function setAccessTokenPath( $accessPath ) {
        # Add the root slash if it's missing.
        if( substr( $accessPath, 0, 1 ) != "/" ) {
            $accessPath		= "/" . $accessPath;
        }

        $this->accesstoken_path	= $accessPath;
    }

    public function setAuthPath( $authPath ) {
        # Add the root slash if it's missing.
        if( substr( $authPath, 0, 1 ) != "/" ) {
            $authPath	= "/" . $authPath;
        }

        $this->auth_path = $authPath;
    }

    public function setCallback( $callbackURI ) {
        $this->callbackUrl = $callbackURI;
    }

    public function setPathsFromConfig(){
        $this->requesttoken_path	= AppConfig::$f1_requesttoken_path;
        $this->accesstoken_path	= AppConfig::$f1_accesstoken_path;
        $this->auth_path = AppConfig::$f1_auth_path;
        $this->callbackUrl = AppConfig::$callbackURI;
    }
    /************* END- Path Setter **********************/

    /************* START- TOKEN GETTERS ******************/
    public function getToken() {
        return $this->requestToken;
    }

    public function getTokenSecret() {
        return $this->tokenSecret;
    }
    /************* END- TOKEN GETTERS ********************/

    public function getBaseUrl() {
        return  $this->baseUrl;
    }

    private function sendRequest( $httpMethod, $requestURL, $nonOAuthHeader = array(), $requestBody = "", $successHttpCode = 201 ) {
        // 0 = call is being made to request a requestToken
        // 1 = call is being made to request an accessToken
        // 2 = call is being made to request a protected resources

        $tokenType = 2;
        $relativePath = str_ireplace($this->baseUrl, "", $requestURL);
        if (strcasecmp($relativePath, $this->requesttoken_path) == 0)
        $tokenType = 0;
        else if(strcasecmp($relativePath, $this->accesstoken_path) == 0)
        $tokenType = 1;

        $oAuthHeader = array();
        $oAuthHeader[] = $this->getOAuthHeader($httpMethod, $requestURL, $tokenType);

        if( $httpMethod == "POST" || $httpMethod == "PUT") {
            curl_setopt( $this->connection, CURLOPT_POST, true );
            if(strlen($requestBody) > 0)
            curl_setopt( $this->connection, CURLOPT_POSTFIELDS, $requestBody );
        } else {
            curl_setopt( $this->connection, CURLOPT_POST, false );
        }

        $httpHeaders = array_merge($oAuthHeader, $nonOAuthHeader);

        if(AppConfig::$simulateRequest) {
            print $this->lineBreak."[--------------------BEGIN Simulate Request for $requestURL----------------------------]".$this->lineBreak;
            $requestSimulator = sprintf( "%s %s HTTP/1/1".$this->lineBreak, $httpMethod, $relativePath );
            foreach ($httpHeaders as $header)
            $requestSimulator .=  $header.$this->lineBreak;

            $requestSimulator .= $requestBody;
            print $requestSimulator;
            print $this->lineBreak."[--------------------END Simulate Request----------------------------]".$this->lineBreak;

            return;
        }
        curl_setopt( $this->connection, CURLOPT_URL, $requestURL );
        curl_setopt( $this->connection, CURLOPT_HTTPHEADER, $httpHeaders );

        $responseBody = curl_exec( $this->connection );
        $this->Debug($responseBody, $requestBody);
        if(!curl_errno( $this->connection)) // If there is no error
        {
            $info = curl_getinfo($this->connection);
            if($info['http_code'] === $successHttpCode) {
                return $responseBody;
            }
            else {
                return null;
            }
        }
        else{
            return null;
        }
    }

    /*
     * Make a request using HTTP GET
     */
    public function doRequest($requestURL, $nonOAuthHeader = array("Accept: application/json"), $successHttpCode = 200) {
        return $this->sendRequest( "GET", $requestURL, $nonOAuthHeader, "", $successHttpCode );
    }

    /*
     * Make a request using HTTP Post
     */
    public function postRequest($requestURL, $requestBody = "", $nonOAuthHeader = array("Accept: application/json",  "Content-type: application/json"), $successHttpCode = 201){
        return $this->sendRequest( "POST", $requestURL, $nonOAuthHeader, $requestBody, $successHttpCode );
    }

    /*
    * Make a request using HTTP PUT
    */
    public function putRequest($requestURL, $requestBody = "", $nonOAuthHeader = array("Accept: application/json",  "Content-type: application/json"), $successHttpCode = 200){
        return $this->sendRequest( "PUT", $requestURL, $nonOAuthHeader, $requestBody, $successHttpCode );
    }

    /**
     *	Get a Request Token from the Service Provider.
     */
    public function getRequestToken() {
        $requestURL	= sprintf( "%s%s",
            $this->baseUrl, $this->requesttoken_path );

        $requestBody	= $this->sendRequest( "POST", $requestURL,  array(), "", 200  );

        preg_match( "~oauth_token\=([^\&]+)\&oauth_token_secret\=([^\&]+)~i", $requestBody, $tokens );
        if( !isset( $tokens[1] ) || !isset( $tokens[2] ) ) {
            return false;
        }

        $this->requestToken = $tokens[1] ;
        $this->tokenSecret = $tokens[2] ;

        return true;
    }

    /**
     *	Get an Access Token from the Service Provider.
     *  @param		oauth_token		The authorized request token. This token
     *                              is returned by the service provider when the user authenticates
     *                              on the service provider side. Use this request token to request a Access token
     */
    public function getAccessToken($oauth_token, $token_secret) {

        $this->requestToken = $oauth_token;
        $this->tokenSecret = $token_secret;

        $requestURL	= sprintf( "%s%s",
            $this->baseUrl, $this->accesstoken_path );

        curl_setopt( $this->connection, CURLOPT_NOBODY, true );
        $requestBody	= $this->sendRequest( "POST", $requestURL,  array(), "", 200  );

        preg_match( "~oauth_token\=([^\&]+)\&oauth_token_secret\=([^\&]+)~i", $requestBody, $tokens );
        if( !isset( $tokens[1] ) || !isset( $tokens[2] ) ) {
            return false;
        }

        $this->requestToken = $tokens[1] ;
        $this->tokenSecret = $tokens[2] ;

        return true;
    }

    /**
     *	Redirect the client's browser to the Service Provider's Authentication page.
     */
    public function AuthenticateUser() {
        // First step is to get the Request Token (oauth_token)
        $this->getRequestToken();
        // Using the oauth_token take the user to Service Provider’s login screen.
        // Also provide a “callback” which the url to which the service provider redirects after the credentials are authenticated at the service provider side.
        if(AppConfig::$includeRequestSecretInUrl) {
            $parts = parse_url($this->callbackUrl);
            $query = $parts['query'];
            if(strlen($query)>0) {
                $this->callbackUrl = $this->callbackUrl.'&oauth_token_secret='.$this->getTokenSecret();
            } else {
                $this->callbackUrl = $this->callbackUrl.'?oauth_token_secret='.$this->getTokenSecret();
            }
        }

        $callbackURI = rawurlencode( $this->callbackUrl );
        // Example: HTTP GET
        // http://dc.apiqa.dev.corp.local/v1/WeblinkUser/Login?oauth_token=fae7dde0-4df8-45cb-952d-6a7431439635&oauth_callback=http%3A%2F%2Flocalhost%2FRESTfulAPIConsumer
        // ( callback is url encoded form of http://localhost/RESTfulAPIConsumer)

        $authenticateURL = sprintf( "%s%s?oauth_token=%s",
            $this->baseUrl, $this->auth_path, $this->requestToken );

        if( !empty( $callbackURI ) ) {
            $authenticateURL	.= sprintf( "&oauth_callback=%s", $callbackURI );
        }

        header( "Location: " . $authenticateURL );
        return true;
    }

    /***************************START- CORE API Functions********************************/
    public function addHousehold($householdFirstName, $householdLastName, $household_create_path){
        $householdName = $householdFirstName.' '.$householdLastName;
        $date = date("Y-m-d");
        // FOR SOME REASON< XML DOESNT WORK. USE JSON INSTEAD
        $requestBody = <<<EOD
    {"household":{"@id":"","@uri":"","@oldID":"","@hCode":"","householdName":"$householdName","householdSortName":"$householdLastName","householdFirstName":"$householdFirstName","lastSecurityAuthorization":null,"lastActivityDate":null,"createdDate":null,"lastUpdatedDate":null}}
EOD;

        // USING CURL
        $requestUrl = $this->baseUrl.$household_create_path;
        $httpHeaders = array(
        "Accept: application/json",  // This is important. This tells the API that it should send the resoinse in form of json
        "Content-type: application/json"); // This tells the API that the incoming request data is in form of json
        // return $this->sendRequest("POST", $requestUrl, $httpHeaders, $requestBody);
        return $this->postRequest($requestUrl, $requestBody);
    }

    public function addPerson($householdId, $householdMemberTypeId, $statusId,$firstName, $lastName, $person_create_path) {
        $requestBody = <<<EOD
{"person":{"@id":"","@uri":"","@oldID":"","@iCode":"","@householdID":"$householdId","@oldHouseholdID":"","title":null,"salutation":null,"prefix":null,"firstName":"$firstName","lastName":"$lastName","suffix":null,"middleName":null,"goesByName":null,"formerName":null,"gender":null,"dateOfBirth":null,"maritalStatus":null,"householdMemberType":{"@id":"$householdMemberTypeId","@uri":"https://ftapiair.staging.fellowshiponeapi.com/v1/People/HouseholdMemberTypes/1","name":"Head"},"isAuthorized":"true","status":{"@id":"$statusId","@uri":"","name":null,"comment":null,"date":null,"subStatus":{"@id":"","@uri":"","name":null}},"occupation":{"@id":"","@uri":"","name":null,"description":null},"employer":null,"school":{"@id":"","@uri":"","name":null},"denomination":{"@id":"","@uri":"","name":null},"formerChurch":null,"barCode":null,"memberEnvelopeCode":null,"defaultTagComment":null,"weblink":{"userID":null,"passwordHint":null,"passwordAnswer":null},"solicit":null,"thank":null,"firstRecord":null,"lastMatchDate":null,"createdDate":null,"lastUpdatedDate":null}}
EOD;
        // USING CURL
        $requestUrl = $this->baseUrl.$person_create_path;

        $httpHeaders = array(
        "Accept: application/json", // This is important. This tells the API that it should send the resoinse in form of json
        "Content-type: application/json"); //This tells the API that the incoming request data is in form of json
        // return $this->sendRequest("POST", $requestUrl, $httpHeaders, $requestBody);
        return $this->postRequest($requestUrl, $requestBody);
    }

    public function getStatuses() {
        $requestUrl = $this->baseUrl.AppConfig::$f1_statuses_list.'?format=json';
        $httpHeaders = array(
        "Accept: application/json"); // This is important. This tells the API that it should send the resoinse in form of json
        // return $this->sendRequest("GET", $requestUrl, $httpHeaders, "", 200);
        return $this->doRequest($requestUrl);
    }

    public function getHouseholdMemberTypes() {
        $requestUrl = $this->baseUrl.AppConfig::$f1_householdMemberTypes_list;
        $httpHeaders = array(
        "Accept: application/json");  // This is important. This tells the API that it should send the resoinse in form of json
        // return $this->sendRequest("GET", $requestUrl, $httpHeaders, "", 200);
        return $this->doRequest($requestUrl);
    }
    /***************************END- CORE API Functions**********************************/

    /*******************START- PRIVATE UTILITY FUNCTIONS ***************/
    public function url_encode($input) {
        if (is_scalar($input)) {
            return str_replace('+', ' ',
                str_replace('%7E', '~', rawurlencode($input)));
        } else {
            return '';
        }
    }

    /**
   * builds the data one would send in a GET/POST request
   * Basically it would create key=value paid seperated by &
   */
    public function build_post_data($oAuthOptions) {
        $total = array();
        foreach ($oAuthOptions as $k => $v) {
            $total[] = $this->url_encode($k) . "=" . $this->url_encode($v);
        }
        $out = implode("&", $total);
        return $out;
    }

    /**
     *	Create a random "nonce" for every oAuth Request.
     */
    private function getOAuthNonce() {
        return md5( microtime() . rand( 500, 1000 ) );
    }

    /**
     *	Create a signature for this oAuth Request.
     *
     *	@param		requestURL		The URL to send your request to.
     *	@param		oAuthOptions	An array of key=>value parameters
     *								to determine the oAuth Options
     *								(and POST data as well).
     */
    private function getOAuthSignature( $httpMethod, $requestURL, $oAuthOptions ) {
        $requestValues		= array();

        foreach( $oAuthOptions as $oAuthKey => $oAuthValue ) {
            if( substr( $oAuthKey, 0, 6 ) != "oauth_" )
            continue;

            if( is_array( $oAuthValue ) ) {
                foreach( $oAuthValue as $valueKey => $value ) {
                    $requestValues[]	= sprintf( "%s=%s", $valueKey, rawurlencode( utf8_encode( $value ) ) );
                }
            } else {
                $requestValues[]	= sprintf( "%s=%s", $oAuthKey, rawurlencode( utf8_encode( $oAuthValue ) ) );
            }
        }

        $requestValues		= implode( "&", $requestValues );
        $requestValues		= rawurlencode( utf8_encode( $requestValues ) );

        $signatureString	= sprintf( "%s&%s&%s",
            $httpMethod, rawurlencode( $requestURL ), $requestValues );

        $secretKey			= sprintf( "%s&%s",
            $this->consumerSecret, $this->tokenSecret );

        $signedString		= hash_hmac( "sha1", $signatureString, $secretKey, true );
        $signedString		= base64_encode( $signedString );
        // return rawurlencode( utf8_encode( $signedString ) );
        return $signedString;

    }

    // Builds OAuthHeader to be sent in a Request Token request. This method can be used
    // in case we want to use "Authorization" Header instead of passing the authorization values
    // in the header
    private function buildOAuthHeader ( $oAuthOptions ) {
        $requestValues		= array();

        foreach( $oAuthOptions as $oAuthKey => $oAuthValue ) {
            if( substr( $oAuthKey, 0, 6 ) != "oauth_" )
            continue;

            if( is_array( $oAuthValue ) ) {
                foreach( $oAuthValue as $valueKey => $value ) {
                    $requestValues[]	= sprintf( "%s=%s", $valueKey, rawurlencode( utf8_encode( $value ) ) );
                }
            } else {
                $requestValues[]	= sprintf( "%s=%s", $oAuthKey, rawurlencode( utf8_encode( $oAuthValue ) ) );
            }
        }

        $requestValues		= implode( ",", $requestValues );

        return $requestValues;
    }

    /*
     * Returns a string of the format Authorization: <auth_string>
     * @param tokenType: Type of token 0==request token. > 0 Access token and other requests
     */
    private function getOAuthHeader ($http_method, $requestURL, $tokenType = 1) {
        $oAuthHeaderValues	= array(
            "oauth_consumer_key"		=> $this->consumerKey,
            "oauth_nonce"				=> $this->getOAuthNonce(),
            "oauth_signature_method"	=> "HMAC-SHA1",
            "oauth_timestamp"			=> mktime(),
            "oauth_version"				=> "1.0"
        );

        if($tokenType > 0) // Its not a request Request Token
        $oAuthHeaderValues["oauth_token"] = $this->requestToken;

        $oAuthHeaderValues["oauth_signature"] = RequestSigner::build_signature($this->consumerSecret, $this->tokenSecret, $http_method, $requestURL, $oAuthHeaderValues);

        $oauthHeader = $this->buildOAuthHeader( $oAuthHeaderValues );

        return sprintf( "Authorization: %s", $oauthHeader);
    }

    private function Debug($responseBody, $requestBody = "" ) {
        if(AppConfig::$debug){
            print $this->lineBreak."[--------------------BEGIN DEBUG----------------------------]".$this->lineBreak;
            $info = curl_getinfo($this->connection, CURLINFO_HEADER_OUT);
            $info_header = curl_getinfo($this->connection);
            print "Request: ".$info.$this->lineBreak.$requestBody.$this->lineBreak."Response: ".$responseBody;
            print "Debug: Url: ".$info_header['url']." Content-Type: ".$info_header['content_type']." HTTP_CODE: ".$info_header['http_code'];
            print $this->lineBreak."[--------------------END DEBUG----------------------------]".$this->lineBreak;
        }
    }
    /*******************END- PRIVATE UTILITY FUNCTIONS ***************/
}
?>
