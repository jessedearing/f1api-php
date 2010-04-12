<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>Callback Landing Page</title>
    </head>
    <body>
        <?php
        require_once 'OAuth\AppConfig.php';
        require_once 'OAuth\OAuthClient.php';
        // THIS ONLY APPLIES FOR 3rd PARTY APPLICATIONS

        // Get the "authenticated" request token here. The Service provider will append this token to the query string when
        // redirecting the user's browser to the Callback page
        $oauth_token = $_GET["oauth_token"];
        // The is the token secret which you got when you requested the request_token
        // You should get this because you appended this token secret when you got redirected to the
        // Service Provider's login screen
        $token_secret = $_GET["oauth_token_secret"];
        print 'oauth_token is: '.$oauth_token.", oauth_token_secret: ".$token_secret.'<br/>';

        $apiConsumer = new OAuthClient(AppConfig::$base_url, AppConfig::$consumer_key, AppConfig::$consumer_secret);
        $success = $apiConsumer->getAccessToken($oauth_token, $token_secret);
        $access_token = $apiConsumer->getToken();
        $token_secret = $apiConsumer->getTokenSecret();
        print "Access token: ".$access_token.", Token Secret: ".$token_secret.'<br/>';
        // STORE THE ACCESS TOKEN AND TOKEN SECRET HERE
        // This may be database or session or some other mechanism based on what you choose

        // If we get the access token successfully, the response header includes the url to get the authenticated user.
        $responseHeaders = $apiConsumer->getResponseHeader();
        print "Response Header: ".implode("<br/>",$responseHeaders);
        // Iterate over the response headers to find the current logged in person
        foreach ($responseHeaders as $val) {
            $start = 'Content-Location:';
            $contentLocation =  substr( $val, 0, 17 );
            if( $contentLocation == $start )
            $personLocation = str_replace($start, "", $val);
        }
        ?>
    </body>
</html>
