<?php
require_once 'Util.php';
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RequestSigner
 *
 * @author jsingh
 */
class RequestSigner {

    public static function build_signature($consumerSecret, $tokenSecret, $http_method, $Url, $oAuthOptions) {
        if(AppConfig::$debug) {
            print AppConfig::$lineBreak.'[------------------START DEBUG: SIGNATURE PARAMETERS--------------------]'.AppConfig::$lineBreak;
        }
        
        $base_string = RequestSigner::get_signature_base_string($http_method, $Url, $oAuthOptions);
        if(AppConfig::$debug) {
            print AppConfig::$lineBreak.'BASE STRING: '.$base_string.AppConfig::$lineBreak;
        }
        $key_parts = array(
            $consumerSecret,
            $tokenSecret
        );

        $key_parts = Util::urlencode_rfc3986($key_parts);
        $key = implode('&', $key_parts);

         if(AppConfig::$debug) {
            print AppConfig::$lineBreak.'[------------------END DEBUG: SIGNATURE PARAMETERS--------------------]'.AppConfig::$lineBreak;
        }
        return base64_encode( hash_hmac('sha1', $base_string, $key, true));
    }

   /**
   * The Signature Base String is a consistent reproducible concatenation of the
   * request elements into a single string. The string is used as an input in hashing or signing algorithms.
   *
   * The base string => $http_method&urlencode($Url without query params)&urlencode(normalized_request_parameters)
   * (Basically HTTP method(GET/POST etc), the URL(scheme://host/path, doesnt include the query string), and the norlalized request parameters
   * each urlencoded and the concated with &.)
   */
    private function get_signature_base_string($http_method, $Url, $oAuthOptions) {
        // Get the Query String parameters. Example if the request is http://photos.example.net/photos.aspx?file=vacation.jpg&size=original
        // then get the query string and create an array from it in form of key value pairs
        // $qsArray     Key     Value
        //              file    vacation.jpg
        //              size    original
        $parts = parse_url($Url);
        $qs = $parts['query'];
        parse_str($qs, $qsArray);
        $signable_options = array_merge($oAuthOptions, $qsArray);
        $signable_parameters = RequestSigner::get_normalized_request_parameters($signable_options);
        $normalized_url = Util::get_normalized_http_url($Url);

        if(AppConfig::$debug) {
            print AppConfig::$lineBreak.'[----------START Parts to build base string from '.$Url.'-----------]'.AppConfig::$lineBreak;
            print AppConfig::$lineBreak.'HTTP Method: '.$http_method.AppConfig::$lineBreak;
            print AppConfig::$lineBreak.'Normalized Url: '.$normalized_url.AppConfig::$lineBreak;
            print AppConfig::$lineBreak.'Signable Parameters: '.$signable_parameters.AppConfig::$lineBreak;
            print AppConfig::$lineBreak.'Query String parameters: '.$qs.AppConfig::$lineBreak;
            print AppConfig::$lineBreak.'[----------END Parts to build base string-----------]'.AppConfig::$lineBreak;
        }

        $parts = array(
            $http_method, // GET or POST
            $normalized_url, // return "$scheme://$host$path";
            $signable_parameters
        );

        // Url encode each of http method, Url(without query string), and the normalized parameters (oauth parameters, along with query string parameters)
        $parts = Util::urlencode_rfc3986($parts);
        // After url encoding, concatenate them with an &
        return implode('&', $parts);
    }

    /*
     * Returns Normalized Request Parameter string
     * @params: Parameters that need to be included in the normalized string
     *          params is an array. params contain all the Parameters that need to be used to generating the normalized parameter string
     *          The Parameters that need to be passed in are:
     *              -- Parameters in the OAUTH Authorization Header (eg: oauth_consumer_key,oauth_nonce, oauth_signature_method, oauth_timestamp etc.)
     *                  NOTE: realm and oauth_signature are not used
     *              -- Parameters included in HTTP POST request body ( WITH CONTENT-TYPE OF application/x-www-form-urlencoded )
     *              -- HTTP GET parameters added to URLs in the query post (eg: for http://www.example.com/resource?id=123&name=jas
     *                 the params array will include 2 entries with id=>123 and name=>jas)
     * These are the following steps followed to generate the normalized request parameter string
     * 1. Encode all the parameters in the $params array (Specifically encode all the keys and values in the $params array)
     * 2. Sort all the entries in the parameters array
     * 3. Concatenate the sorted entried into a single string. a) For each key in the array, key is seperated from the value by an '=' character (creating a name value pair of form name=value)
     *    EVEN IF THE VALUE IS EMPTY. b) Each name value pair is seperated by an '&' character
     *
     * Example: User used HTTP GET to requrest an image
     * http://photos.example.net/photos.aspx?file=vacation.jpg&size=original
     * params will contain: Key                     value
     *                      oauth_consumer_key      7
                            oauth_nonce             d9678981968d24da32602222727a8c1f
                            oauth_signature_method  HMAC-SHA1
                            oauth_timestamp			1241025870
                            oauth_version           1.0
     *                      oauth_token             61344b1a-0e88-4ccc-8854-cc6219d83642
     *                      file                    vacation.jpg
     *                      size                    original
     */
    private static function get_normalized_request_parameters($params) {

        // Remove oauth_signature if present
        if (isset($params['oauth_signature'])) {
            unset($params['oauth_signature']);
        }

        // STEP 1: Urlencode both keys and values
        $keys = Util::urlencode_rfc3986(array_keys($params));
        $values = Util::urlencode_rfc3986(array_values($params));
        $params = array_combine($keys, $values);

        // STEP 2: Sort by keys (natsort)
        uksort($params, 'strcmp');

        // STEP 3. Concatenate the sorted entried into a single string
        // 3a) Generate key=value pairs
        $pairs = array();
        foreach ($params as $key=>$value ) {
            if (is_array($value)) {
                // If the value is an array, it's because there are multiple
                // with the same key, sort them, then add all the pairs
                natsort($value);
                foreach ($value as $v2) {
                    $pairs[] = $key . '=' . $v2;
                }
            } else {
                $pairs[] = $key . '=' . $value;
            }
        }

        // 3b) Return the pairs, concated with &
        return implode('&', $pairs);
    }
}
?>
