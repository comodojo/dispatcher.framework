<?php namespace comodojo\ObjectResult;

class ObjectSuccess implements ObjectResultInterface {

	private $service = NULL;

	private $code = 200;

	private $supported_success_codes = Array(200,202,204);

	private $content = NULL;

	private $headers = Array();

	private $contentType = "text/plain";

	public function setService($service) {

		$this->service = $service;

		return $this;

	}

	public function getService() {

		return $this->service;

	}

	public function setStatusCode($code) {

		$code = filer_var($code, FILTER_VALIDATE_INT);

		$this->code = in_array($code, $this->supported_success_codes) ? $code : $this->code;

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

	public function setHeader($header, $value=NULL) {

		$this->headers[$header] = $value;

		return $this;

	}

	public function getHeader($header) {

		if ( isset($this->headers[$header]) ) return $this->headers[$header];

		return false;

	}

	public function unsetHeader($header) {

		if ( isset($this->headers[$header]) ) unset($this->headers[$header]);

		return $this;

	}

	public function setHeaders($headers) {

		$this->headers = is_array($headers) ? $headers : $this->header;

		return $this;

	}

	public function getHeaders() {

		return $this->headers;

	}

	public function unsetHeaders() {

		$this->headers = Array();

		return $this;

	}

	public function setContentType($type) {

		$this->contentType = $type;

		return $this;

	}

	public function getContentType() {

		return $this->contentType;

	}

}

?>