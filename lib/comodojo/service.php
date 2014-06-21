<?php namespace comodojo;

use \comodojo\Exception\DispatcherException;
//use \comodojo\Exception\IOException;
//use \comodojo\Exception\DatabaseException;

class service {

	// Things a service should define:

	private $content_type = "text/plain";

	private $status_code = 200;

	private $headers = Array();

	// Thins a service could use for free

	public $attributes = Array();

	public $parameters = Array();

	public $raw_parameters = Array();

	public $serialize = NULL;

	public $deserialize = NULL;

	// Things a service may define

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

	private $methods = NULL;

	// Things dedicated to internal use

	private $supported_success_codes = Array(200,202,204);

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

	/**
	 * Expected attributes (i.e. ones that will build the URI)
	 *
	 * @param 	string 	$method 	HTTP method for punctual attributes rematch or ANY
	 * @param 	array 	$parameters An array of parameters, with or without compliance check
	 */
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

		return $this;

	}

	/**
	 * Liked (optional) attributes
	 *
	 * @param 	string 	$method 	HTTP method for punctual attributes rematch or ANY
	 * @param 	array 	$parameters An array of parameters, with or without compliance check
	 */
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

		return $this;

	}

	public final function setContentType($type) {

		$this->content_type = $type;

		return $this;

	}

	public final function getContentType($type) {

		return $this->content_type;

	}

	public final function setStatusCode($code) {

		$code = filter_var($code, FILTER_VALIDATE_INT);

		$this->status_code = in_array($code, $this->supported_success_codes) ? $code : $this->code;

		return $this;

	}

	public final function getStatusCode($code) {

		return $this->status_code;

	}

	/**
	 * Set header component
	 *
	 * @param 	string 	$header 	Header name
	 * @param 	string 	$value 		Header content (optional)
	 *
	 * @return 	ObjectRequest 	$this
	 */
	public function setHeader($header, $value=NULL) {

		$this->headers[$header] = $value;

		return $this;

	}

	/**
	 * Unset header component
	 *
	 * @param 	string 	$header 	Header name
	 *
	 * @return 	bool
	 */
	public function unsetHeader($header) {

		if ( isset($this->headers[$header]) ) {

			unset($this->headers[$header]); 

			return true;

		}

		return false;

	}

	/**
	 * Get header component
	 *
	 * @param 	string 	$header 	Header name
	 *
	 * @return 	string 	Header component in case of success, false otherwise
	 */
	public function getHeader($attribute) {

		if ( isset($this->headers[$header]) ) return $this->headers[$header];

		return false;

	}

	/**
	 * Set headers
	 *
	 * @param 	array 	$headers 	Headers array
	 *
	 * @return 	ObjectRequest 	$this
	 */
	public function setHeaders($attributes) {

		$this->headers = is_array($headers) ? $headers : $this->header;

		return $this;

	}

	/**
	 * Unset headers
	 *
	 * @return 	ObjectRequest 	$this
	 */
	public function unsetHeaders() {

		$this->headers = Array();

		return $this;

	}

	/**
	 * Get headers
	 *
	 * @return 	Array 	Headers array
	 */
	public function getHeaders() {

		return $this->headers;

	}	

	public final function getMethods() {

		$supported_methods = explode(',',DISPATCHER_SUPPORTED_METHODS);

		$implemented_methods = Array();

		foreach ( $supported_methods as $method ) {

			if (method_exists($this, strtolower($method))) array_push($return,$method);

		}

		return $return;

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