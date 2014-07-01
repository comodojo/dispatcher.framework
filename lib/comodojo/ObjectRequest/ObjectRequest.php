<?php namespace comodojo\ObjectRequest;

/**
 * The Object request class
 *
 * This is the class use to model the request and exposed by level2(/3) event(s)
 * "dispatcher.request(.*)"
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

class ObjectRequest {

	/**
	 * Current time, as provided by dispatcher
	 *
	 * @var	integer
	 */
	private $current_time = NULL;

	/**
	 * Service name (first sub in querystring or "service=" in standard operational mode)
	 *
	 * @var	string
	 */
	private $service = NULL;

	/**
	 * The HTTP method (GET,POST,...) 
	 *
	 * @var	string
	 */
	private $method = NULL;

	/**
	 * Attributes (querystring) 
	 *
	 * @var	array
	 */
	private $attributes = Array();

	/**
	 * Parameters (POST) 
	 *
	 * @var	array
	 */
	private $parameters = Array();

	/**
	 * Raw parameters (php://input) 
	 *
	 * @var	array
	 */
	private $raw_parameters = NULL;

	/**
	 * Request headers
	 *
	 * @var	array
	 */
	private $headers = Array();

	/**
	 * Set current time
	 *
	 * @param	integer	$time	
	 *
	 * @return	Object	$this
	 */
	public function setCurrentTime($time) {

		$this->current_time = $time;

		return $this;

	}

	/**
	 * Get current time
	 *
	 * @return	integer
	 */
	public function getCurrentTime() {
		
		return $this->current_time;

	}

	/**
	 * Set service 
	 *
	 * @param	strinng	$service	
	 *
	 * @return	Object	$this
	 */
	public function setService($service) {

		$this->service = is_string($service) ? $service : $this->service;

		return $this;

	}

	/**
	 * Get service name
	 *
	 * @return	string
	 */
	public function getService() {
		
		return $this->service;

	}

	/**
	 * Set HTTP method
	 *
	 * @param	string	$method
	 *
	 * @return	Object	$this
	 */
	public function setMethod($method) {
		
		$this->method = is_string($method) ? strtoupper($method) : $this->method;

		return $this;

	}

	/**
	 * Get HTTP method
	 *
	 * @return	string	
	 */
	public function getMethod() {
		
		return $this->method;

	}

	/**
	 * Set single attribute
	 *
	 * @param	string	$name
	 * @param	scalar	$value
	 *
	 * @return	Object	$this
	 */
	public function setAttribute($name=NULL, $value) {

		if ( is_null($name) ) array_push($this->attributes, $value);

		else $this->attributes[$name] = $value;

		return $this;

	}

	/**
	 * Unset single attribute
	 *
	 * @param	string	$attribute
	 *
	 * @return	Object	$this
	 */
	public function unsetAttribute($attribute) {

		if ( array_key_exists($attribute, $this->attributes) ) {

			unset($this->attributes[$attribute]);

			return true;

		}

		return false;

	}

	/**
	 * Get single attribute
	 *
	 * @param	string	$attribute
	 *
	 * @return	scalar|NULL	Attribute value or NULL if not found
	 */
	public function getAttribute($attribute) {

		if ( array_key_exists($attribute, $this->attributes) ) {

			return $this->attributes[$attribute];

		}

		return NULL;

	}

	/**
	 * Set attributes (all in one shot)
	 *
	 * @param	array	$attributes
	 *
	 * @return	Object	$this
	 */
	public function setAttributes($attributes) {

		$this->attributes = is_array($attributes) ? $attributes : $this->attributes;

		return $this;

	}

	/**
	 * Unset attributes
	 *
	 * @return	bool
	 */
	public function unsetAttributes() {

		$this->attributes = Array();

		return true;

	}

	/**
	 * Get attributes
	 *
	 * @return	array
	 */
	public function getAttributes() {

		return $this->attributes;

	}

	/**
	 * Set single parameter
	 *
	 * @param	string	$name
	 * @param	scalar	$value
	 *
	 * @return	Object	$this
	 */
	public function setParameter($name, $value) {

		$this->parameters[$name] = $value;

		return $this;

	}

	/**
	 * Unset single parameter
	 *
	 * @param	string	$parameter
	 *
	 * @return	Object	$this
	 */
	public function unsetParameter($parameter) {

		if ( array_key_exists($parameter, $this->parameters) ) {

			unset($this->parameters[$parameter]);

			return true;

		}

		return false;

	}

	/**
	 * Get single parameter
	 *
	 * @param	string	$parameter
	 *
	 * @return	scalar|NULL	Parameter value or NULL if not found
	 */
	public function getParameter($parameter) {

		if ( array_key_exists($parameter, $this->parameters) ) {

			return $this->parameters[$parameter];

		}

		return NULL;

	}

	/**
	 * Set parameters (all in one shot)
	 *
	 * @param	array	$parameters
	 *
	 * @return	Object	$this
	 */
	public function setParameters($parameters) {

		$this->parameters = is_array($parameters) ? $parameters : $this->parameters;

		return $this;

	}

	/**
	 * Unset parameters
	 *
	 * @return	bool
	 */
	public function unsetParameters() {

		$this->parameters = Array();

		return true;

	}

	/**
	 * Get parameters
	 *
	 * @return	array
	 */
	public function getParameters() {

		return $this->parameters;

	}

	/**
	 * Set raw parameters
	 *
	 * @return	Object	$this
	 */
	public function setRawParameters($parameters) {

		$this->raw_parameters = $parameters;

		return $this;

	}
	
	/**
	 * Get raw parameters
	 *
	 * @return	string
	 */
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