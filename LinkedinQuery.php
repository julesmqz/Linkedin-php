<?php
class LinkedinQuery {
	private $_access_token = null;

	public function __construct($access_token = null) {
		$this->_access_token = $access_token;
	}

	public function set_access_token($access_token) {
		if ((is_null($access_token) || $access_token === '')) {
			throw new Exception("Access Token not valid.");

		} else {
			$this->_access_token = $access_token;
		}
	}

	public function fetch($method, $resource, $params = array()) {
		//print $_SESSION['access_token'];
		if ((is_null($this->_access_token) || $this->_access_token === '')) {
			throw new Exception("You need to set an access token.");

		} else {
			$opts = array(
				'http' => array(
					'method' => $method,
					'header' => "Authorization: Bearer " . $this->_access_token . "\r\n" . "x-li-format: json\r\n",
				)
			);
			// Need to use HTTPS
			$url = 'https://api.linkedin.com' . $resource;

			// Append query parameters (if there are any)
			if (count($params)) {$url .= '?' . http_build_query($params);}

			// Tell streams to make a (GET, POST, PUT, or DELETE) reque
			// And use OAuth 2 access token as Authorizati
			$context = stream_context_create($opts);

			// Hocus Poc
			$response = file_get_contents($url, false, $context);

			// Native PHP object, please
			return json_decode($response);
		}

	}
}