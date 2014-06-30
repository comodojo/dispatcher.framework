<?php namespace comodojo\ObjectResult;

/**
 * The Object success class, an implementation of ObjectResultInterface
 *
 * @package 	Comodojo dispatcher (Spare Parts)
 * @author		comodojo <info@comodojo.org>
 * @license 	GPL-3.0+
 *
 * LICENSE:
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

class ObjectSuccess implements ObjectResultInterface {

	private $service = NULL;

	private $code = 200;

	private $content = NULL;

	private $headers = Array();

	private $contentType = "text/plain";

	private $charset = DISPATCHER_DEFAULT_ENCODING;

	private $supported_success_codes = Array(200,202,204);

	public function setService($service) {

		$this->service = $service;

		return $this;

	}

	public function getService() {

		return $this->service;

	}

	public function setStatusCode($code) {

		$code = filter_var($code, FILTER_VALIDATE_INT);

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