<?php namespace comodojo;


class header {

	private $headers = Array();

	private $current_time = NULL;

	public final function __construct($time=false) {

		$this->current_time = $time !== false ? $time : time();

		debug(' + Header up and running; current time: '.$this->current_time,'INFO','header');

	}

	public final function set($header, $value=NULL) {

		$this->headers[$header] = $value;

	}

	public final function get($header) {

		if ( array_key_exists($header, $this->headers) ) return $this->headers[$header];
		else return false;

	}

	public final function free() {

		$this->headers = Array();

	}

	public final function compose($status, $contentLength=0, $value=false) {

		switch ($status) {

			case 200: //OK

				if ( array_key_exists("ttl", $this->headers) ) {

					if ( $this->headers["ttl"] > 0 ) {
						header('Cache-Control: max-age='.$this->headers["ttl"].', must-revalidate');
						header('Expires: '.gmdate("D, d M Y H:i:s", $this->current_time + $this->headers["ttl"])." GMT");
					}
					elseif ($this->headers["ttl"] == 0) {
						header('Cache-Control: no-cache, must-revalidate');
						header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
					}
					else {
						//null
					}

					unset($this->headers["ttl"]);

				}
				
				if ( array_key_exists("contentType", $this->headers) ) {

					if ( array_key_exists("charset", $this->headers) ) {
						header('Content-type: '.strtolower($this->headers["contentType"]).'; charset='.$this->headers["charset"]);
						unset($this->headers["charset"]);
					}
					else {
						header('Content-type: '.strtolower($this->headers["contentType"]));
					}
					unset($this->headers["contentType"]);

				}

				if ($contentLength !== 0) header('Content-Length: '.$contentLength);

				$this->process_extra($this->headers);

				break;

			case 202: //Accepted

				//PLEASE NOTE: according to HTTP/1.1, 202 header SHOULD HAVE status description in body... just in case
				header($_SERVER["SERVER_PROTOCOL"].' 202 Accepted');
				header('Status: 202 Accepted');

				if ($contentLength !== 0) header('Content-Length: '.$contentLength);

				$this->process_extra($this->headers);

				break;

			case 204: //OK - No Content

				header($_SERVER["SERVER_PROTOCOL"].' 204 No Content');
				header('Status: 204 No Content');
				header('Content-Length: 0',true);

				$this->process_extra($this->headers);
			
				break;

			case 201: //Created
			case 301: //Moved Permanent
			case 302: //Found
			case 303: //See Other
			case 307: //Temporary Redirect

				header("Location: ".$value,true,$statusCode);
				//if ($contentLength !== 0) header('Content-Length: '.$contentLength); //is it needed?

				break;

			case 304: //Not Modified

				if ( $value === false ) header($_SERVER["SERVER_PROTOCOL"].' 304 Not Modified');
				else header('Last-Modified: '.gmdate('D, d M Y H:i:s', $value).' GMT', true, 304);

				if ($contentLength !== 0) header('Content-Length: '.$contentLength); //is it needed?

				break;

			case 400: //Bad Request

				header($_SERVER["SERVER_PROTOCOL"].' 400 Bad Request', true, 400);
				if ($contentLength !== 0) header('Content-Length: '.$contentLength);
			
				break;

			case 403:

				header('Origin not allowed', true, 403); //Not originated from allowed source

				break;

			case 404: //Not Found

				header($_SERVER["SERVER_PROTOCOL"].' 404 Not Found');
				header('Status: 404 Not Found');

				break;

			case 405: //Not allowed

				header('Allow: ' . $value, true, 405); 

				break;

			case 500: //Internal Server Error

				header('500 Internal Server Error', true, 500);

				break;

			case 501: //Not implemented

				header('Allow: ' . $value, true, 501);
			
				break;

		}

	}

	private function process_extra($headers) {

		foreach ( $headers as $header => $value ) {

			if ( is_null($value) ) header($header, true);
			else  header($header.": ".$value, true);

		}

	}

}

?>