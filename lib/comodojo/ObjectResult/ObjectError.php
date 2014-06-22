<?php namespace comodojo\ObjectResult;

class ObjectError implements ObjectResultInterface {

	private $service = NULL;

	private $code = 500;

	private $supported_error_codes = Array(400,403,404,405,500,501,503);

	private $content = NULL;

	private $headers = Array();

	private $contentType = "text/plain";

	private $charset = DISPATCHER_DEFAULT_ENCODING;

	public function setService($service) {

		$this->service = $service;

		return $this;

	}

	public function getService() {

		return $this->service;

	}

	public function setStatusCode($code) {

		$code = filter_var($code, FILTER_VALIDATE_INT);

		$this->code = in_array($code, $this->supported_error_codes) ? $code : $this->code;

		return $this;

	}

	public function getStatusCode() {

		return $this->code;

	}

	public function setContent($message) {

		$this->content = $message;

		return $this;

	}

	public function getContent() {

		return $this->content;

	}

	public function setLocation($location) {}

	public function getLocation() {}

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

	public function setContentType($type) {

		$this->contentType = $type;

		return $this;

	}

	public function getContentType() {

		return $this->contentType;

	}

	public function setCharset($type) {

		$this->charset = $type;

		return $this;

	}

	public function getCharset() {

		return $this->charset;

	}

}

?>