<?php

//
// Fellowship Tech >> OAuth Library
// Created by Jaskaran Singh
//

//
// Generic URL handling utilities.
//
class Util {
	public static function urlencode_rfc3986($input) {
		if (is_array($input)) {
			return array_map(array('Util','urlencode_rfc3986'), $input);
		} else if (is_scalar($input)) {
			return str_replace('+', ' ',
				str_replace('%7E', '~', rawurlencode($input)));
		} else {
			return '';
		}
	}

	public function get_normalized_http_url($http_url) {
		$parts = parse_url($http_url);
		$port = @$parts['port'];
		$scheme = $parts['scheme'];
		$host = $parts['host'];
		$path = @$parts['path'];

		$port or $port = ($scheme === 'https') ? '443' : '80';

		if (($scheme === 'https' && $port !== '443') || ($scheme === 'http' && $port !== '80')) {
			$host = $host . ':' . $port;
		}

		return $scheme . '://' . $host . $path;
	}

	public static function get_port($http_url) {
		$parts = parse_url($http_url);
		$port = @$parts['port'];
		$scheme = $parts['scheme'];
		$port or $port = ($scheme === 'https') ? '443' : '80';
		return $port;
	}

	public static function get_host_name($http_url) {
		$parts = parse_url($http_url);
		$host = $parts['host'];
		return $host;
	}

	public static function get_guid() {
		// The field names refer to RFC 4122 section 4.1.2 - http://www.ietf.org/rfc/rfc4122.txt
		return sprintf('%04x%04x-%04x-%03x4-%04x-%04x%04x%04x',
			mt_rand(0, 65535),
			mt_rand(0, 65535),
			mt_rand(0, 65535),
			mt_rand(0, 4095),
			bindec(substr_replace(sprintf('%016b', mt_rand(0, 65535)), '01', 6, 2)),
			mt_rand(0, 65535),
			mt_rand(0, 65535),
			mt_rand(0, 65535)
		);
	}
}

//
// Signs outgoing URL requests.
//
class Request_signer {
	public static function build_signature($consumer_secret, $token_secret, $http_method, $url, $oauth_options) {
		$base_string = Request_signer::get_signature_base_string($http_method, $url, $oauth_options);

		$key_parts = array(
			$consumer_secret,
			$token_secret
		);

		$key_parts = Util::urlencode_rfc3986($key_parts);
		$key = implode('&', $key_parts);

		return base64_encode(hash_hmac('sha1', $base_string, $key, true));
	}

	// Consistent reproducible concatenation of request elements into a single string.
	private function get_signature_base_string($http_method, $url, $oauth_options) {
		$parts = parse_url($url);
		$qs = $parts['query'];

		parse_str($qs, $qs_array);

		$signable_options = array_merge($oauth_options, $qs_array);
		$signable_parameters = Request_signer::get_normalized_request_parameters($signable_options);
		$normalized_url = Util::get_normalized_http_url($url);

		$parts = array(
			$http_method,
			$normalized_url,
			$signable_parameters
		);

		$parts = Util::urlencode_rfc3986($parts);

		return implode('&', $parts);
	}

	// Returns normalized request parameter string.
	private static function get_normalized_request_parameters($params) {

		// Remove oauth_signature if present.
		if (isset($params['oauth_signature'])) {
			unset($params['oauth_signature']);
		}

		// Step 1: Encode both keys and values.
		$keys = Util::urlencode_rfc3986(array_keys($params));
		$values = Util::urlencode_rfc3986(array_values($params));
		$params = array_combine($keys, $values);

		// Step 2: Sort by keys.
		uksort($params, 'strcmp');

		// Step 3. Concatenate the sorted entries.
		$pairs = array();

		// Step 3.A. Generate key / value pairs.
		foreach ($params as $key=>$value) {
			// Sort out multiple values with the same key.
			if (is_array($value)) {
				natsort($value);
				foreach ($value as $v2) {
					$pairs[] = $key . '=' . $v2;
				}
			} else {
				$pairs[] = $key . '=' . $value;
			}
		}

		// Step 3.B. Return the pairs, concated with '&'.
		return implode('&', $pairs);
	}
}


//
// OAuth client library.
//
class Oauth_api_client {
	private $consumer_key = null;
	private $consumer_secret = null;
	private $request_token;
	private $token_secret = '';
	private $base_url = null;
	private $request_token_path = null;
	private $access_token_path = null;
	private $auth_path = null;
	private $connection;

	public function __construct($base_url, $church_code, $consumer_key, $consumer_secret) {
		$this->consumer_key = $consumer_key;
		$this->consumer_secret = $consumer_secret;
		$this->base_url = str_replace('{church_code}', $church_code, $base_url);
		$this->init_curl();
	}

	private function init_curl() {
		// Create new cURL connection.
		$this->connection = curl_init();

		// Prepare the cURL connection.
		curl_setopt($this->connection, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($this->connection, CURLOPT_POST, true);
		curl_setopt($this->connection, CURLOPT_UPLOAD, false);
		curl_setopt($this->connection, CURLINFO_HEADER_OUT, true);
		curl_setopt($this->connection, CURLOPT_SSL_VERIFYPEER, false);
	}

	// Initialize the access token and token secret. Make this call to set the
	// access token and token secret before accessing any protected resources.
	public function init_access_token($access_token, $token_secret) {
		$this->request_token = $access_token;
		$this->token_secret = $token_secret;
	}

	public function set_request_token_path($request_path) {
		// Add the root slash if it's missing.
		if (substr($request_path, 0, 1) !== '/') {
			$request_path	= '/' . $request_path;
		}

		$this->request_token_path = $request_path;
	}

	public function set_access_token_path($access_path) {
		// Add the root slash if it's missing.
		if (substr($access_path, 0, 1) !== '/') {
			$access_path = '/' . $access_path;
		}

		$this->access_token_path	= $access_path;
	}

	public function set_auth_path($auth_path) {
		// Add the root slash if it's missing.
		if (substr($auth_path, 0, 1) !== '/') {
			$auth_path	= "/" . $auth_path;
		}

		$this->auth_path = $auth_path;
	}

	public function set_paths_from_config(){
		$this->request_token_path = App_config::$f1_request_token_path;
		$this->access_token_path = App_config::$f1_access_token_path;
		$this->auth_path = App_config::$f1_auth_path;
	}

	public function get_token() {
		return $this->request_token;
	}

	public function get_token_secret() {
		return $this->token_secret;
	}

	public function get_base_url() {
		return $this->base_url;
	}

	private function send_request($http_method, $request_url, $non_oauth_header = array(), $request_body = '', $successHttpCode = 201) {
		// 0 = Call made to fetch a request token.
		// 1 = Call made to fetch an access token.
		// 2 = Call made to fetch a protected resource.

		$token_type = 2;
		$relative_path = str_ireplace($this->base_url, '', $request_url);
		if (strcasecmp($relative_path, $this->request_token_path) === 0) {
			$token_type = 0;
		}
		else if (strcasecmp($relative_path, $this->access_token_path) === 0) {
			$token_type = 1;
		}

		$oauth_header = array();
		$content_type_header = array();
		if ($http_method === 'POST' || $http_method === 'PUT') {
			if (strlen(App_config::$accept_header) > 0) {
				$content_type_header = array(App_config::$accept_header, App_config::$content_type);
			}

			curl_setopt($this->connection, CURLOPT_POST, true);

			if (strlen($request_body) > 0) {
				curl_setopt($this->connection, CURLOPT_POSTFIELDS, $request_body);
			}
		} else {
			if (strlen(App_config::$accept_header) > 0) {
				$content_type_header = array(App_config::$accept_header);
			}
			curl_setopt($this->connection, CURLOPT_POST, false);
		}

		$non_oauth_header = array_merge($non_oauth_header, $content_type_header);

		$parts = parse_url($request_url);
		$qs = $parts['query'];

		if ($qs === '') {
			$request_url .= '?';
		} else {
			$request_url .= '&';
		}

		$oauth_header_vals = $this->get_oauth_header($http_method, $request_url, $token_type);
		$request_url .= $this->build_oauth_header($oauth_header_vals, '&');
		$http_headers = $non_oauth_header;

		curl_setopt($this->connection, CURLOPT_HTTPHEADER, $http_headers);
		curl_setopt($this->connection, CURLOPT_URL, $request_url);

		$response_body = curl_exec($this->connection);

		if (!curl_errno($this->connection)) {
			$info = curl_getinfo($this->connection);

			if ($info['http_code'] === $successHttpCode) {
				return $response_body;
			} else {
				return null;
			}
		} else{
			return null;
		}
	}

	// Make request using HTTP GET.
	public function do_request($request_url, $non_oauth_header = array(), $successHttpCode = 200) {
		return $this->send_request('GET', $request_url, $non_oauth_header, '', $successHttpCode);
	}

	private function get_oauth_nonce() {
		return md5(microtime() . rand(500, 1000));
	}

	private function build_oauth_header ($oauth_options, $seperator = ',') {
		$request_values = array();

		foreach($oauth_options as $oauth_key => $oauth_value) {
			if (substr($oauth_key, 0, 6) !== 'oauth_')
			continue;

			if (is_array($oauth_value)) {
				foreach($oauth_value as $valueKey => $value) {
					$request_values[] = sprintf('%s=%s', $valueKey, rawurlencode(utf8_encode($value)));
				}
			} else {
				$request_values[] = sprintf('%s=%s', $oauth_key, rawurlencode(utf8_encode($oauth_value)));
			}
		}

		$request_values = implode($seperator, $request_values);
		return $request_values;
	}

	// Returns OAuth header array.
	private function get_oauth_header ($http_method, $request_url, $token_type = 1) {
		$oauth_header_values = array(
			'oauth_consumer_key' => $this->consumer_key,
			'oauth_nonce' => $this->get_oauth_nonce(),
			'oauth_signature_method' => 'HMAC-SHA1',
			'oauth_timestamp' => mktime(),
			"oauth_version" => '1.0'
		);

		if ($token_type > 1) {
			$oauth_header_values['oauth_token'] = $this->request_token;
		}

		$oauth_header_values['oauth_signature'] = Request_signer::build_signature($this->consumer_secret, $this->token_secret, $http_method, $request_url, $oauth_header_values);
		return $oauth_header_values;
	}

}

?>