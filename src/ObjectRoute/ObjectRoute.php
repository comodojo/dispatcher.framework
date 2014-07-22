<?php namespace Comodojo\Dispatcher\ObjectRoute;

/**
 * The ObjectRoute class
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

class ObjectRoute {

    private $service = null;

    private $target = null;

    private $class = null;

    private $type = "ERROR";

    private $redirect_code = 307;

    private $error_code = 503;

    private $cache = false;

    private $ttl = false;

    private $headers = Array();

    private $access_control = null;

    private $parameters = Array();

    private $supported_route_types = Array("ROUTE","REDIRECT","ERROR");

    private $supported_redirect_codes = Array(201,301,302,303,307);

    private $supported_error_codes = Array(400,403,404,405,500,501,503);

    private $supported_cache_modes = Array("SERVER","CLIENT","BOTH");

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
     * Set route type
     *
     * @param   string  $type
     *
     * @return  Object  $this
     */
    public function setType($type) {

        $type = strtoupper($type);

        $this->type = in_array($type, $this->supported_route_types) ? $type : $this->type;

        return $this;

    }

    /**
     * Get route type
     *
     * @return  string
     */
    public function getType() {

        return $this->type;

    }

    /**
     * Set route target
     *
     * @param   string  $target
     *
     * @return  Object  $this
     */
    public function setTarget($target) {

        $this->target = $target;

        return $this;

    }

    /**
     * Get route target
     *
     * @return  string
     */
    public function getTarget() {

        return $this->target;

    }

    public function setClass($class) {

        $this->class = $class;

        return $this;

    }

    public function getClass() {

        return $this->class;

    }

    public function setRedirectCode($code) {

        $code = filter_var($code, FILTER_VALIDATE_INT);

        $this->redirect_code = in_array($code, $this->supported_redirect_codes) ? $code : $this->redirect_code;

        return $this;

    }

    public function getRedirectCode() {

        return $this->redirect_code;

    }

    public function setErrorCode($code) {

        $code = filter_var($code, FILTER_VALIDATE_INT);

        $this->error_code = in_array($code, $this->supported_error_codes) ? $code : $this->error_code;

        return $this;

    }

    public function getErrorCode() {

        return $this->error_code;

    }

    public function setCache($cache) {

        if ($cache == false) {

            $this->cache = false;

        }
        else {

            $cache = strtoupper($cache);

            $this->cache = in_array($cache, $this->supported_cache_modes) ? $cache : $this->cache;

        }

        return $this;
        
    }

    public function getCache() {

        return $this->cache;
    }

    public function setTtl($ttl) {

        $ttl = filter_var($ttl, FILTER_VALIDATE_INT);

        $this->ttl = is_int($ttl) ? $ttl : $this->ttl;

        return $this;

    }

    public function getTtl() {

        return $this->ttl;

    }

    public function setAccessControl($control) {

        $this->access_control = $control;

        return $this;
    }

    public function getAccessControl() {

        return $this->access_control;

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
     * @return  string  Header component in case of success, false otherwise
     */
    public function getHeader($header) {

        if ( isset($this->headers[$header]) ) return $this->headers[$header];

        return false;

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
     * Set extra parameter
     *
     * @return  ObjectRequest   $this
     */
    public function setParameter($parameter, $value=null) {

        $this->parameters[$parameter] = $value;

        return $this;

    }

    /**
     * Get extra parameter
     *
     * @return  Array   Headers array
     */
    public function getParameter($parameter) {

        if ( isset($this->parameters[$parameter]) ) return $this->parameters[$parameter];

        else return null;

    }

    /**
     * Get extra parameters
     *
     * @return  Array   Headers array
     */
    public function getParameters() {

        return $this->parameters;

    }

}