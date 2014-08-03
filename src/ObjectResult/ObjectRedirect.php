<?php namespace Comodojo\Dispatcher\ObjectResult;

/**
 * The Object redirect class, an implementation of ObjectResultInterface
 *
 * @package     Comodojo dispatcher (Spare Parts)
 * @author      Marco Giovinazzi <info@comodojo.org>
 * @license     GPL-3.0+
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

class ObjectRedirect implements ObjectResultInterface {

    private $service = null;

    private $code = 307;

    private $supported_redirect_codes = Array(201,301,302,303,307);

    private $location = null;

    private $headers = Array();

    /**
     * Set service name
     *
     * @param   string  $service    The service name
     *
     * @return  Object  $this
     */
    public function setService($service) {

        $this->service = $service;

        return $this;

    }

    /**
     * Get service name
     *
     * @return  string
     */
    public function getService() {

        return $this->service;

    }

    /**
     * Set status code
     *
     * @param   integer $code
     *
     * @return  Object  $this
     */
    public function setStatusCode($code) {

        $code = filter_var($code, FILTER_VALIDATE_INT);

        $this->code = in_array($code, $this->supported_redirect_codes) ? $code : $this->code;

        return $this;

    }

    /**
     * Get status code
     *
     * @return  integer
     */
    public function getStatusCode() {

        return $this->code;

    }

    /**
     * STUB method: no content in a redirect!
     */
    public function setContent($message) {}

    /**
     * STUB method: no content in a redirect!
     */
    public function getContent() {}

    /**
     * Set location for REDIRECT
     *
     * @param   string  $location
     *
     * @return  Object  $this
     */
    public function setLocation($location) {

        $location = filter_var($location, FILTER_VALIDATE_URL);

        $this->location = $location !== false ? $location : $this->location;

        return $this;

    }

    /**
     * Get location (in redirect)
     *
     * @return  string
     */
    public function getLocation() {

        return $this->location;

    }

    /**
     * Set header component
     *
     * @param   string  $header     Header name
     * @param   string  $value      Header content (optional)
     *
     * @return  ObjectRequest   $this
     */
    public function setHeader($header, $value=null) {

        $this->headers[$header] = $value;

        return $this;

    }

    /**
     * Unset header component
     *
     * @param   string  $header     Header name
     *
     * @return  bool
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
     * @param   string  $header     Header name
     *
     * @return  string  Header component in case of success, null otherwise
     */
    public function getHeader($header) {

        if ( isset($this->headers[$header]) ) return $this->headers[$header];

        return null;

    }

    /**
     * Set headers
     *
     * @param   array   $headers    Headers array
     *
     * @return  ObjectRequest   $this
     */
    public function setHeaders($headers) {

        $this->headers = is_array($headers) ? $headers : $this->header;

        return $this;

    }

    /**
     * Unset headers
     *
     * @return  ObjectRequest   $this
     */
    public function unsetHeaders() {

        $this->headers = Array();

        return $this;

    }

    /**
     * Get headers
     *
     * @return  Array   Headers array
     */
    public function getHeaders() {

        return $this->headers;

    }

    /**
     * STUB method: no content type in a redirect!
     */
    public function setContentType($type) {}

    /**
     * STUB method: no content type in a redirect!
     */
    public function getContentType() {}

    /**
     * STUB method: no charset in a redirect!
     */
    public function setCharset($type) {}

    /**
     * STUB method: no charset in a redirect!
     */
    public function getCharset() {}

}