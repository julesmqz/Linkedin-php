<?php
class LinkedinOAuth {
	private $_api_key = null;
	private $_api_secret = null;
	private $_redirect_url = null;
	private $_scope = 'r_basicprofile';
	private $_state = 0;
	private $_access = array();

	public function __construct($api_key = null, $api_secret = null, $redirect_url = null, $scope = 'r_basicprofile') {
		$this->_api_key = $api_key;
		$this->_api_secret = $api_secret;
		$this->_redirect_url = $redirect_url;
		$this->_scope = $scope;
	}

	public function set_keys($api_key, $api_secret) {
		if ((is_null($api_key) || $api_key === '') || (is_null($api_secret) || $api_secret === '')) {
			throw new Exception("Api Key and Api Secret not valid.");
		} else {
			$this->_api_key = $api_key;
			$this->_api_secret = $api_secret;
		}
	}

	public function set_redirect_url($redirect_url) {
		if ((is_null($redirect_url) || $redirect_url === '')) {
			throw new Exception("Redirect Url is not valid.");
		} else {
			$this->_redirect_url = $redirect_url;
		}
	}

	public function set_scope($scope) {
		if ((is_null($scope) || $scope === '')) {
			throw new Exception("Scope is not valid.");
		} else {
			$this->_scope = $scope;
		}
	}

	public function set_state($state) {
		if ((is_null($state) || $state === '')) {
			throw new Exception("State is not valid.");
		} else {
			$this->_state = $state;
		}
	}

	private function _ready() {
		if (is_null($this->_api_key) || is_null($this->_api_secret) || is_null($this->_redirect_url)) {
			return false;
		} else {
			return true;
		}
	}

	public function get_login_url() {
		if ($this->_ready() === false) {
			throw new Exception("Default values have not been set (API KEY, API SECRET or REDIRECT URL).");
		} else {
			$params = array(
				'response_type' => 'code',
				'client_id' => $this->_api_key,
				'scope' => $this->_scope,
				'state' => uniqid('', true), // unique long string
				'redirect_uri' => REDIRECT_URI
			);
			// Authentication request
			$url = 'https://www.linkedin.com/uas/oauth2/authorization?' . http_build_query($params);
			// Needed to identify request when it returns to us
			$this->_state = $params['state'];
			return array('url' => $url, 'state' => $params['state']);
		}
	}

	public function get_state() {
		return $this->_state;
	}

	function get_access_token($code) {
		$params = array(
			'grant_type' => 'authorization_code',
			'client_id' => $this->_api_key,
			'client_secret' => $this->_api_secret,
			'code' => $code, //Get code
			'redirect_uri' => $this->_redirect_url
		);
		// Access Token request
		$url = 'https://www.linkedin.com/uas/oauth2/accessToken?' . http_build_query($params);

		// Tell streams to make a POST request
		$context = stream_context_create(
			array('http' => array('method' => 'POST')
			)
		);
		try {
			// Retrieve access token information
			$response = file_get_contents($url, false, $context);
			// Native PHP object, please
			$token = json_decode($response);

			// Store access token and expiration time

			if (!is_null($token)) {
				$this->_access = array(

					'token' => $token->access_token, // guard this!
					'expires_in' => $token->expires_in, // relative time (in seconds)
					'expires_at' => time() + $token->expires_in//absolute time
				);
			}

			return $this->_access;
		} catch (Exception $e) {
			die('Exception: ' . $e->get_message());
		}

	}
}