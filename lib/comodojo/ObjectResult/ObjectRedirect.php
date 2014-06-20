<?php namespace comodojo\ObjectResult;

class ObjectRedirect implements ObjectResultInterface {

	private $service = NULL;

	private $code = 307;

	private $supported_redirect_codes = Array(201,301,302,303,307);

	private $location = NULL;

	private $headers = Array();

	public function setService($service) {

		$this->service = $service;

		return $this;

	}

	public function getService() {

		return $this->service;

	}

	public function setStatusCode($code) {

		$code = filer_var($code, FILTER_VALIDATE_INT);

		$this->code = in_array($code, $this->supported_redirect_codes) ? $code : $this->code;

		return $this;

	}

	public function getStatusCode() {

		return $this->code;

	}

	public function setContent($message) {}

	public function getContent() {}

	public function setLocation($location) {

		$location = filter_var($location, FILTER_VALIDATE_URL);

		$this->location = $location !== false ? $location : $this->location;

		return $this;

	}

	public function getLocation() {

		return $this->location;

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

	public function setContentType($type) {}

	public function getContentType() {}

}

?>