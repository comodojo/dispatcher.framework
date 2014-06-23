<?php namespace comodojo\ObjectRequest;

class ObjectRequest {

	private $current_time = NULL;

	private $service = NULL;

	private $method = NULL;

	private $attributes = Array();

	private $parameters = Array();

	private $raw_parameters = NULL;

	private $headers = Array();

	public function setCurrentTime($time) {

		$this->current_time = $time;

		return $this;

	}

	public function getCurrentTime() {
		
		return $this->current_time;

	}

	public function setService($service) {

		$this->service = is_string($service) ? $service : $this->service;

		return $this;

	}

	public function getService() {
		
		return $this->service;

	}

	public function setMethod($method) {
		
		$this->method = is_string($method) ? strtoupper($method) : $this->method;

		return $this;

	}

	public function getMethod() {
		
		return $this->method;

	}

	public function setAttribute($name=NULL, $value) {

		if ( is_null($name) ) array_push($this->attributes, $value);

		else $this->attributes[$name] = $value;

		return $this;

	}

	public function unsetAttribute($attribute) {

		if ( array_key_exists($attribute, $this->attributes) ) {

			unset($this->attributes[$attribute]);

			return true;

		}

		return false;

	}

	public function getAttribute($attribute) {

		if ( array_key_exists($attribute, $this->attributes) ) {

			return $this->attributes[$attribute];

		}

		return NULL;

	}

	public function setAttributes($attributes) {

		$this->attributes = is_array($attributes) ? $attributes : $this->attributes;

		return $this;

	}

	public function unsetAttributes() {

		$this->attributes = Array();

		return true;

	}

	public function getAttributes() {

		return $this->attributes;

	}

	public function setParameter($name, $value) {

		$this->parameters[$name] = $value;

		return $this;

	}

	public function unsetParameter($parameter) {

		if ( array_key_exists($parameter, $this->parameters) ) {

			unset($this->parameters[$parameter]);

			return true;

		}

		return false;

	}

	public function getParameter($parameter) {

		if ( array_key_exists($parameter, $this->parameters) ) {

			return $this->parameters[$parameter];

		}

		return NULL;

	}

	public function setParameters($parameters) {

		$this->parameters = is_array($parameters) ? $parameters : $this->parameters;

		return $this;

	}

	public function unsetParameters() {

		$this->parameters = Array();

		return true;

	}

	public function getParameters() {

		return $this->parameters;

	}

	public function setRawParameters($parameters) {

		$this->raw_parameters = $parameters;

		return $this;

	}
	
	public function getRawParameters() {

		return $this->raw_parameters;

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

}

?>