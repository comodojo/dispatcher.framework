<?php namespace comodojo\Dispatcher\ObjectResult;

/**
 * The ObjectResultInterface, base interface that any result class should implement
 *
 * @package		Comodojo dispatcher (Spare Parts)
 * @author		comodojo <info@comodojo.org>
 * @license		GPL-3.0+
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

interface ObjectResultInterface {

	/**
	 * Set service name
	 *
	 * @param	string	$service	The service name
	 *
	 * @return	Object	$this
	 */
	public function setService($service);

	/**
	 * Get service name
	 *
	 * @return	string
	 */
	public function getService();

	/**
	 * Set status code
	 *
	 * @param	integer	$code
	 *
	 * @return	Object	$this
	 */
	public function setStatusCode($code);

	/**
	 * Get status code
	 *
	 * @return	integer
	 */
	public function getStatusCode();

	/**
	 * Set result content (http body)
	 *
	 * @param	string	$message
	 *
	 * @return	Object	$this
	 */
	public function setContent($message);

	/**
	 * Get result content
	 *
	 * @return	string
	 */
	public function getContent();

	/**
	 * Set location for REDIRECT
	 *
	 * @param	string	$location
	 *
	 * @return	Object	$this
	 */
	public function setLocation($location);

	/**
	 * Get location (in redirect)
	 *
	 * @return	string
	 */
	public function getLocation();

	/**
	 * Set header component
	 *
	 * @param 	string 	$header 	Header name
	 * @param 	string 	$value 		Header content (optional)
	 *
	 * @return 	ObjectRequest 	$this
	 */
	public function setHeader($header, $value);

	/**
	 * Get header component
	 *
	 * @param 	string 	$header 	Header name
	 *
	 * @return 	string 	Header component in case of success, false otherwise
	 */
	public function getHeader($header);

	/**
	 * Unset header component
	 *
	 * @param 	string 	$header 	Header name
	 *
	 * @return 	bool
	 */
	public function unsetHeader($header);

	/**
	 * Set headers
	 *
	 * @param 	array 	$headers 	Headers array
	 *
	 * @return 	ObjectRequest 	$this
	 */
	public function setHeaders($headers);

	/**
	 * Get headers
	 *
	 * @return 	Array 	Headers array
	 */
	public function getHeaders();

	/**
	 * Unset headers
	 *
	 * @return 	ObjectRequest 	$this
	 */
	public function unsetHeaders();

	/**
	 * Set content type
	 *
	 * @param	string	$type
	 *
	 * @return	Object	$this
	 */
	public function setContentType($type);

	/**
	 * Get content type
	 *
	 * @return	strinng
	 */
	public function getContentType();

	/**
	 * Set charset
	 *
	 * @param	string	$type
	 *
	 * @return	Object	$this
	 */
	public function setCharset($type);

	/**
	 * Get charset 
	 *
	 * @return	string
	 */
	public function getCharset();

}

?>