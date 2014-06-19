<?php namespace comodojo;

class service {

	public $serialize = NULL;

	public $deserialize = NULL;

	private $methods = NULL;

	private $requires_get = Array();

	private $likes_get = Array();

	private $requires_put = Array();

	private $likes_put = Array();

	private $requires_post = Array();

	private $likes_post = Array();

	private $requires_delete = Array();

	private $likes_delete = Array();

	private $requires_any = Array();

	private $likes_any = Array();

	private $content_type = "text/plain";

	private $status_code = 200;

	private $headers = Array();

	private $access_control = NULL;

	private $raw_parameters = false;

	/*************** HTTP METHODS IMPLEMENTATIONS **************/
	
	/**
	 * Implement this method if your service should support
	 * HTTP-GET requests
	 */
	//public function get($attributes) {}
	
	/**
	 * Implement this method if your service should support
	 * HTTP-POST requests
	 */
	// public function post($attributes) {}
	
	/**
	 * Implement this method if your service should support
	 * HTTP-PUT requests
	 */
	// public function put($attributes) {}
	
	/**
	 * Implement this method if your service should support
	 * HTTP-DELETE requests
	 */
	// public function delete($attributes) {}
	
	/**
	 * Implement this method if your service should support
	 * any HTTP requests (it's quite a wildcard, please be careful...)
	 */
	// public function logic($attributes) {}
	
	/*************** HTTP METHODS IMPLEMENTATIONS **************/
	
	/******************* OVERRIDABLE METHODS *******************/

	public function setup() {

	}

	public function error() {

		//$this->status = 

	}

	/******************* OVERRIDABLE METHODS *******************/

	public final function __construct() {

		$this->serialize = new serialization();

		$this->deserialize = new deserialization();

	}

	public final function expect($method, $parameters) {

		switch (strtoupper($method)) {

			case 'GET':
				$this->requires_get = $parameters;
				break;
			
			case 'PUT':
				$this->requires_put = $parameters;
				break;
			
			case 'POST':
				$this->requires_post = $parameters;
				break;
			
			case 'DELETE':
				$this->requires_delete = $parameters;
				break;

			case 'ANY':
				$this->requires_any = $parameters;
				break;

		}

	}

	public final function like($method, $parameters) {

		switch (strtoupper($method)) {

			case 'GET':
				$this->likes_get = $parameters;
				break;
			
			case 'PUT':
				$this->likes_put = $parameters;
				break;
			
			case 'POST':
				$this->likes_post = $parameters;
				break;
			
			case 'DELETE':
				$this->likes_delete = $parameters;
				break;

			case 'ANY':
				$this->likes_any = $parameters;
				break;

		}

	}

	public final function content($type) {

		$this->content_type = $type;

	}

	public final function status($code) {

		$this->status_code = filter_var($code, FILTER_VALIDATE_INT);

	}

	public final function header($param, $value=NULL) {

		//if (in_array($param, $this->headers)) unset($this->headers[$param]);

		$this->headers[$param] = $value;

	}

	public final function accessControl($location) {

		if ( is_null($this->access_control) ) $this->access_control = Array();

		array_push($this->access_control, $location);

	}

	public final function rawParameters($mode=true) {

		$this->raw_parameters = filter_var($mode, FILTER_VALIDATE_BOOLEAN);

	}

	public final function get_methods() {

		$supported_methods = explode(',',DISPATCHER_SUPPORTED_METHODS);

		$implemented_methods = Array();

		foreach ( $supported_methods as $method ) {

			if (method_exists($this, strtolower($method))) array_push($return,$method);

		}

		return $return;

	}

	public final function get_headers() {

		return $this->headers;

	}

	public final function expected($method) {

		switch ($method) {

			case 'GET':
				$r = $this->require_get;
				break;
			
			case 'PUT':
				$r = $this->require_put;
				break;
			
			case 'POST':
				$r = $this->require_post;
				break;
			
			case 'DELETE':
				$r = $this->require_delete;
				break;

			default:
				$r = Array();
				break;

		}

		return ( sizeof($r) == 0 AND sizeof($this->requires_any) != 0 ) ? $this->requires_any : $r;

	}

	public final function liked($method) {

		switch ($method) {

			case 'GET':
				$r = $this->likes_get;
				break;
			
			case 'PUT':
				$r = $this->likes_put;
				break;
			
			case 'POST':
				$r = $this->likes_post;
				break;
			
			case 'DELETE':
				$r = $this->likes_delete;
				break;

			default:
				$r = Array();
				break;

		}

		return ( sizeof($r) == 0 AND sizeof($this->likes_any) != 0 ) ? $this->likes_any : $r;

	}

}


?>