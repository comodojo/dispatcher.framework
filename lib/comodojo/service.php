<?php namespace comodojo;

class service {

	public $serialize = NULL;

	public $deserialize = NULL;

	private $methods = NULL;

	private $require_get = Array();

	private $require_put = Array();

	private $require_post = Array();

	private $require_delete = Array();

	private $require_any = Array();

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

	public final function require($method, $parameters) {

		switch (strtoupper($method)) {

			case 'GET':
				$this->require_get = $parameters;
				break;
			
			case 'PUT':
				$this->require_put = $parameters;
				break;
			
			case 'POST':
				$this->require_post = $parameters;
				break;
			
			case 'DELETE':
				$this->require_delete = $parameters;
				break;

			case 'ANY':
				$this->require_any = $parameters;
				break;

		}

	}

	public final function content($type) {

		$this->content_type = $type;

	}

	public final function status($code) {

		$this->status_code = filter_var($code, FILTER_VALIDATE_INT);

	}

	public final function header($param, $value) {

		if (in_array($param, $this->headers)) unset($this->headers[$param]);

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

		if (method_exists($this, 'any')) {

			$this->methods = SUPPORTED_METHODS;
			$return = explode(',',SUPPORTED_METHODS);

		}
		else {

			$return = Array();

			foreach ( explode(',',strtoupper(SUPPORTED_METHODS)) as $method ) {
				if (method_exists($this, strtolower($method))) array_push($return,$method);
			}
			$this->methods = implode(',',$return);

		}

		return $return;

	}

	public final function required($method) {

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

		return ( sizeof($r) == 0 AND sizeof($this->require_any) != 0 ) ? $this->require_any : $r;

	}

}


?>