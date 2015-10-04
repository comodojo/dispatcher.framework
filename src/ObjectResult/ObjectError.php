<?php namespace Comodojo\Dispatcher\ObjectResult;

/**
 * The Object error class, an implementation of ObjectResultInterface
 *
 * @package     Comodojo dispatcher
 * @author      Marco Giovinazzi <marco.giovinazzi@comodojo.org>
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

class ObjectError implements ObjectResultInterface {

    private $service = null;

    private $code = 500;

    private $supported_error_codes = Array(400,403,404,405,500,501,503);

    private $content = null;

    private $headers = Array();

    private $contentType = "text/plain";

    private $charset = DISPATCHER_DEFAULT_ENCODING;

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

        $this->code = in_array($code, $this->supported_error_codes) ? $code : $this->code;

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
     * Set result content (http body)
     *
     * @param   string  $message
     *
     * @return  Object  $this
     */
    public function setContent($message) {

        $this->content = $message;

        return $this;

    }

    /**
     * Get result content
     *
     * @return  string
     */
    public function getContent() {

        return $this->content;

    }

    /**
     * StUB method: no location in error!
     */
    public function setLocation($location) {}

    /**
     * StUB method: no location in error!
     */
    public function getLocation() {}

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

        $this->headers = is_array($headers) ? $headers : $this->headers;

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
     * Set content type
     *
     * @param   string  $type
     *
     * @return  Object  $this
     */
    public function setContentType($type) {

        $this->contentType = $type;

        return $this;

    }

    /**
     * Get content type
     *
     * @return  strinng
     */
    public function getContentType() {

        return $this->contentType;

    }

    /**
     * Set charset
     *
     * @param   string  $type
     *
     * @return  Object  $this
     */
    public function setCharset($type) {

        $this->charset = $type;

        return $this;

    }

    /**
     * Get charset 
     *
     * @return  string
     */
    public function getCharset() {

        return $this->charset;

    }

}