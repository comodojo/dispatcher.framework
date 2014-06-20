<?php namespace comodojo\ObjectRoute;

class ObjectRoute {

	private $service = NULL;

	private $target = NULL;

	private $type = "ERROR";

	private $redirect_code = 307;

	private $error_code = 503;

	private $cache = false;

	private $ttl = false;

	private $headers = Array();

	private $access_control = NULL;

	private $supported_route_types = Array("ROUTE","REDIRECT","ERROR");

	private $supported_redirect_codes = Array(201,301,302,303,307);

	private $supported_error_codes = Array(400,403,404,405,500,501,503);

	private $supported_cache_modes = Array("SERVER","CLIENT","BOTH");

	public function setService($service) {

		$this->service = $service;

		return $this;

	}

	public function getService() {

		return $this->service;
	}

	public function setType($type) {

		$type = strtoupper($type);

		$this->type = in_array($type, $this->supported_route_types) ? $type : $this->type;

		return $this;

	}

	public function getType() {

		return $this->type;

	}

	public function setTarget($target) {

		$this->target = $target;

		return $this;

	}

	public function getTarget() {

		return $this->target;

	}

	public function setRedirectCode($code) {

		$code = filter_var($code, FILTER_VALIDATE_INT);

		$this->redirect_code = in_array($code, $this->supported_redirect_codes) ? $code : $this->redirect_code;

		return $this;

	}

	public function getRedirectCode() {

		return $this->redirect_code;

	}

	public function setErrorCode($code) {

		$code = filter_var($code, FILTER_VALIDATE_INT);

		$this->error_code = in_array($code, $this->supported_error_codes) ? $code : $this->error_code;

		return $this;

	}

	public function getErrorCode() {

		return $this->error_code;

	}

	public function setCache($cache) {

		if ($cache == false) {

			$this->cache = false;

		}
		else {

			$cache = strtoupper($cache);

			$this->cache = in_array($cache, $this->supported_cache_modes) ? $cache : $this->cache;

		}

		return $this;
		
	}

	public function getCache() {

		return $this->cache;
	}

	public function setTtl($ttl) {

		$ttl = filter_var($ttl, FILTER_VALIDATE_INT);

		$this->ttl = is_int($ttl) ? $ttl : $this->ttl;

		return $this;

	}

	public function getTtl() {

		return $this->ttl;

	}

	public function setAccessControl($control) {

		$this->access_control = $control;

		return $this;
	}

	public function getAccessControl() {

		return $this->access_control;

	}

	/**
	 * Set header component
	 *
	 * @param 	string 	$header 	Header name
	 * @param 	string 	$value 		Header content (optional)
	 *
	 * @return 	ObjectRequest 	$this
	 */
	public function setHeader($header) {

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
	private function getHeader($attribute) {

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
	private function setHeaders($attributes) {

		$this->headers = is_array($headers) ? $headers : $this->header;

		return $this;

	}

	/**
	 * Unset headers
	 *
	 * @return 	ObjectRequest 	$this
	 */
	private function unsetHeaders() {

		$this->headers = Array();

		return $this;

	}

	/**
	 * Get headers
	 *
	 * @return 	Array 	Headers array
	 */
	private function getHeaders() {

		return $this->headers;

	}

}

?>