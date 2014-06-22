<?php namespace comodojo;

use \comodojo\Exception\DispatcherException;
//use \comodojo\Exception\IOException;
//use \comodojo\Exception\DatabaseException;

class service {

	// Things a service should define:

	private $content_type = "text/plain";

	private $status_code = 200;

	private $headers = Array();

	private $supported_http_methods = DISPATCHER_SUPPORTED_METHODS;

	private $charset = DISPATCHER_DEFAULT_ENCODING;

	// Thins a service could use for free

	public $attributes = Array();

	public $parameters = Array();

	public $raw_parameters = Array();

	public $serialize = NULL;

	public $deserialize = NULL;

	// Things a service may define

	// Expected attributes
	private $expected_attributes = Array(
		"GET"	=>	Array(),
		"PUT"	=>	Array(),
		"POST"	=>	Array(),
		"DELETE"=>	Array(),
		"ANY"	=>	Array()
	);

	// Expected parameters
	private $expected_parameters = Array(
		"GET"	=>	Array(),
		"PUT"	=>	Array(),
		"POST"	=>	Array(),
		"DELETE"=>	Array(),
		"ANY"	=>	Array()
	);

	// Liked attributes
	private $liked_attributes = Array(
		"GET"	=>	Array(),
		"PUT"	=>	Array(),
		"POST"	=>	Array(),
		"DELETE"=>	Array(),
		"ANY"	=>	Array()
	);

	// Liked parameters
	private $liked_parameters = Array(
		"GET"	=>	Array(),
		"PUT"	=>	Array(),
		"POST"	=>	Array(),
		"DELETE"=>	Array(),
		"ANY"	=>	Array()
	);

	// Things dedicated to internal use

	private $supported_success_codes = Array(200,202,204);

	/*************** HTTP METHODS IMPLEMENTATIONS **************/
	
	/**
	 * Implement this method if your service should support
	 * HTTP-GET requests
	 */
	//public function get() {}
	
	/**
	 * Implement this method if your service should support
	 * HTTP-POST requests
	 */
	// public function post() {}
	
	/**
	 * Implement this method if your service should support
	 * HTTP-PUT requests
	 */
	// public function put() {}
	
	/**
	 * Implement this method if your service should support
	 * HTTP-DELETE requests
	 */
	// public function delete() {}
	
	/**
	 * Implement this method if your service should support
	 * any HTTP requests (it's quite a wildcard, please be careful...)
	 */
	// public function logic() {}
	
	/*************** HTTP METHODS IMPLEMENTATIONS **************/
	
	/******************* OVERRIDABLE METHODS *******************/

	public function setup() {

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
	public final function expects($method, $attributes, $parameters=Array()) {

		$method = strtoupper($method);

		$this->expected_attributes[$method] = $attributes;
		$this->expected_parameters[$method] = $parameters;

		return $this;

	}

	/**
	 * Liked (optional) attributes
	 *
	 * @param 	string 	$method 	HTTP method for punctual attributes rematch or ANY
	 * @param 	array 	$parameters An array of parameters, with or without compliance check
	 */
	public final function likes($method, $attributes, $parameters=Array()) {

		$method = strtoupper($method);

		$this->liked_attributes[$method] = $attributes;
		$this->liked_parameters[$method] = $parameters;

		return $this;

	}

	public final function setSupportedMethods($methods) {

		$methods = preg_replace('/\s+/', '', $methods);
		$methods = explode(",", $methods);

		$supported_methods = Array();

		foreach ($methods as $method) {
			
			array_push($supported_methods, strtoupper($method));

		}

		$this->supported_http_methods = implode(",", $supported_methods);

		return $this;

	}

	public final function setContentType($type) {

		$this->content_type = $type;

		return $this;

	}

	public final function getContentType() {

		return $this->content_type;

	}

	public final function setCharset($type) {

		$this->charset = $type;

		return $this;

	}

	public final function getCharset() {

		return $this->charset;

	}

	public final function setStatusCode($code) {

		$code = filter_var($code, FILTER_VALIDATE_INT);

		$this->status_code = in_array($code, $this->supported_success_codes) ? $code : $this->code;

		return $this;

	}

	public final function getStatusCode() {

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
	public function getHeader($header) {

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
	public function setHeaders($headers) {

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

	public final function getSupportedMethods() {

		return $this->supported_http_methods;

	}

	public final function getImplementedMethods() {

		$supported_methods = explode(',',$this->supported_http_methods);

		$implemented_methods = Array();

		foreach ( $supported_methods as $method ) {

			if (method_exists($this, strtolower($method))) array_push($implemented_methods,$method);

		}

		return $implemented_methods;

	}

	public final function getExpected($method) {

		$method = strtoupper($method);

		return Array(

			( sizeof($this->expected_attributes[$method]) == 0 AND sizeof($this->expected_attributes["ANY"]) != 0 ) ? $this->expected_attributes["ANY"] : $this->expected_attributes[$method],

			( sizeof($this->expected_parameters[$method]) == 0 AND sizeof($this->expected_parameters["ANY"]) != 0 ) ? $this->expected_parameters["ANY"] : $this->expected_parameters[$method]			

		);

	}

	public final function getLiked($method) {

		$method = strtoupper($method);

		return Array(

			( sizeof($this->liked_attributes[$method]) == 0 AND sizeof($this->liked_attributes["ANY"]) != 0 ) ? $this->liked_attributes["ANY"] : $this->liked_attributes[$method],

			( sizeof($this->liked_parameters[$method]) == 0 AND sizeof($this->liked_parameters["ANY"]) != 0 ) ? $this->liked_parameters["ANY"] : $this->liked_parameters[$method]			

		);

	}

	public final function getAttributes() {

		return $this->attributes;

	}

	public final function getParameters($raw=false) {

		return $raw ? $this->rawparameters : $this->parameters;
		
	}

}


?>