<?php namespace comodojo\ObjectResult;

	
interface ObjectResultInterface {

	public function setService($service);

	public function getService();

	public function setStatusCode($code);

	public function getStatusCode();

	public function setContent($message);

	public function getContent();

	public function setLocation($location);

	public function getLocation();

	public function setHeader($header, $value);

	public function getHeader($header);

	public function unsetHeader($header);

	public function setHeaders($headers);

	public function getHeaders();

	public function unsetHeaders();

	public function setContentType($type);

	public function getContentType();

}

?>