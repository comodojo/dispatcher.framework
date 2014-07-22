<?php namespace Comodojo\Dispatcher\Service;

/**
 * The Service base class, feel free to extend
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

use \Comodojo\Exception\DispatcherException;
use \Comodojo\Exception\IOException;
use \Comodojo\Dispatcher\Serialization;
use \Comodojo\Dispatcher\Deserialization;


class Service {

    //###### Things a service should define ######//

    /**
     * The content type that service will return.
     * Default content is "text/plain"; each service can override,
     * both in setup phase or in method declaration.
     *
     * @var     string
     */
    private $content_type = "text/plain";

    /**
     * St status code that service will return (in case of no exceptions).
     * Default content is 200 - OK; each service can override,
     * both in setup phase or in method declaration.
     *
     * @var     integer
     */
    private $status_code = 200;

    /**
     * An array of headers that will be returned. This value is initially populated
     * with headers declared in routing phase (if any).
     *
     * @var     array
     */
    private $headers = Array();

    /**
     * Supported HTTP methods (comma separated, not spaced)
     *
     * @var     string
     * @see     dispatcher-config.php
     */
    private $supported_http_methods = DISPATCHER_SUPPORTED_METHODS;

    /**
     * Result charset 
     *
     * @var     string
     * @see     dispatcher-config.php
     */
    private $charset = DISPATCHER_DEFAULT_ENCODING;

    //###### Thins a service could use for free ######//

    /**
     * Request attributes, populated at runtime by dispatcher 
     *
     * @var     array
     */
    public $attributes = Array();

    /**
     * Request parameters, populated at runtime by dispatcher 
     *
     * @var     array
     */
    public $parameters = Array();

    /**
     * Request raw parameters (php://input), populated at runtime by dispatcher 
     *
     * @var     array
     */
    public $raw_parameters = Array();

    /**
     * An instance of dispatcher serializer
     *
     * @var     Object
     */
    public $serialize = NULL;

    /**
     * An instance of dispatcher deserializer
     *
     * @var     Object
     */
    public $deserialize = NULL;

    /**
     * Logger, injected by dispatcher
     *
     * @var float
     */
    private $logger = null;

    //###### Things a service may define ######//

    // Expected attributes
    private $expected_attributes = Array(
        "GET"   =>  Array(),
        "PUT"   =>  Array(),
        "POST"  =>  Array(),
        "DELETE"=>  Array(),
        "ANY"   =>  Array()
        );

    // Expected parameters
    private $expected_parameters = Array(
        "GET"   =>  Array(),
        "PUT"   =>  Array(),
        "POST"  =>  Array(),
        "DELETE"=>  Array(),
        "ANY"   =>  Array()
        );

    // Liked attributes
    private $liked_attributes = Array(
        "GET"   =>  Array(),
        "PUT"   =>  Array(),
        "POST"  =>  Array(),
        "DELETE"=>  Array(),
        "ANY"   =>  Array()
        );

    // Liked parameters
    private $liked_parameters = Array(
        "GET"   =>  Array(),
        "PUT"   =>  Array(),
        "POST"  =>  Array(),
        "DELETE"=>  Array(),
        "ANY"   =>  Array()
        );

    //###### Things dedicated to internal use ######//

    /**
     * Supported success code (as defined in ObjectSuccess)
     *
     * @var     Array
     */
    private $supported_success_codes = Array(200,202,204);

    /*************** HTTP METHODS IMPLEMENTATIONS **************/
    
    /**
     * Implement this method if your service should support
     * HTTP-GET requests
     */
    //public function get() {}
    
    /**
     * Implement this method if your service should support
     * HTTP-POST requests
     */
    // public function post() {}
    
    /**
     * Implement this method if your service should support
     * HTTP-PUT requests
     */
    // public function put() {}
    
    /**
     * Implement this method if your service should support
     * HTTP-DELETE requests
     */
    // public function delete() {}
    
    /**
     * Implement this method if your service should support
     * any HTTP requests (it's quite a wildcard, please be careful...)
     */
    // public function any() {}
    
    /*************** HTTP METHODS IMPLEMENTATIONS **************/
    
    /******************* OVERRIDABLE METHODS *******************/

    /**
     * Service setup.
     *
     * It is the first method that dispatcher will call and could be used
     * to define service parameters in global scope, such as contentType or
     * success HTTP code.
     *
     * PLEASE REMEMBER: setting same parameters in method declaration will
     * override first ones.
     *
     * @return null
     */
    public function setup() {

    }

    /**
     * Service constructor.
     *
     * Currently, only for init serialized/deserialized but can be extended to
     * do anything service needs.
     *
     * PLEASE REMEMBER to call parent::__construct() at the end of your method
     *
     * @return null
     */
    public function __construct($logger) {

        $this->logger = $logger;

        $this->serialize = new Serialization();

        $this->deserialize = new Deserialization();

    }

    /******************* OVERRIDABLE METHODS *******************/

    /**
     * Expected attributes (i.e. ones that will build the URI)
     *
     * @param   string  $method     HTTP method for punctual attributes rematch or ANY
     * @param   array   $parameters An array of parameters, with or without compliance check
     * @return  Object  $this
     */
    final public function expects($method, $attributes, $parameters=Array()) {

        $method = strtoupper($method);

        $this->expected_attributes[$method] = $attributes;
        $this->expected_parameters[$method] = $parameters;

        return $this;

    }

    /**
     * Liked (optional) attributes
     *
     * @param   string  $method     HTTP method for punctual attributes rematch or ANY
     * @param   array   $parameters An array of parameters, with or without compliance check
     * @return  Object  $this
     */
    final public function likes($method, $attributes, $parameters=Array()) {

        $method = strtoupper($method);

        $this->liked_attributes[$method] = $attributes;
        $this->liked_parameters[$method] = $parameters;

        return $this;

    }

    /**
     * Set methods service will support.
     *
     * In can be misleading, but supported HTTP methods and implemented HTTP methods 
     * are not the same thing.
     *
     * - If method is not SUPPORTED, service will not be initiated and a 405 - Not Allowed
     *   error will be returned.
     * - If method is not IMPLEMENTED - i.e. get() method is not defined - service will not
     *   be initiated and a 501 - Not Implemented error will be returned
     *
     * @param   string  $method     HTTP method for punctual attributes rematch or ANY
     * @param   array   $parameters An array of parameters, with or without compliance check
     * @return  Object  $this
     */
    final public function setSupportedMethods($methods) {

        $methods = preg_replace('/\s+/', '', $methods);
        $methods = explode(",", $methods);

        $supported_methods = Array();

        foreach ($methods as $method) {
            
            array_push($supported_methods, strtoupper($method));

        }

        $this->supported_http_methods = implode(",", $supported_methods);

        return $this;

    }

    /**
     * Set service content type
     *
     * @param   string  $type   Content Type
     * @return  Object  $this
     */
    final public function setContentType($type) {

        $this->content_type = $type;

        return $this;

    }

    /**
     * Get service declared content type
     *
     * @return  string  Content Type
     */
    final public function getContentType() {

        return $this->content_type;

    }

    /**
     * Set service charset
     *
     * @param   string  $type   Charset
     * @return  Object  $this
     */
    final public function setCharset($charset) {

        $this->charset = $charset;

        return $this;

    }

    /**
     * Get service declared charset
     *
     * @return  string  Charset
     */
    final public function getCharset() {

        return $this->charset;

    }

    /**
     * Set success status code
     *
     * @param   integer $code   HTTP status code (in case of success)
     * @return  Object  $this
     */
    final public function setStatusCode($code) {

        $code = filter_var($code, FILTER_VALIDATE_INT);

        $this->status_code = in_array($code, $this->supported_success_codes) ? $code : $this->code;

        return $this;

    }

    /**
     * Get service-defined status code
     *
     * @return  integer     HTTP status code (in case of success)
     */
    final public function getStatusCode() {

        return $this->status_code;

    }

    /**
     * Get dispatcher logger
     *
     * @return  Object
     */
    final public function getLogger() {

        return $this->logger;

    }

    /**
     * Set header component
     *
     * @param   string  $header     Header name
     * @param   string  $value      Header content (optional)
     *
     * @return  Object  $this
     */
    final public function setHeader($header, $value=NULL) {

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
    final public function unsetHeader($header) {

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
    final public function getHeader($header) {

        if ( isset($this->headers[$header]) ) return $this->headers[$header];

        return false;

    }

    /**
     * Set headers
     *
     * @param   array   $headers    Headers array
     *
     * @return  Object  $this
     */
    final public function setHeaders($headers) {

        $this->headers = is_array($headers) ? $headers : $this->header;

        return $this;

    }

    /**
     * Unset headers
     *
     * @return  Object  $this
     */
    final public function unsetHeaders() {

        $this->headers = Array();

        return $this;

    }

    /**
     * Get headers
     *
     * @return  Array   Headers array
     */
    final public function getHeaders() {

        return $this->headers;

    }

    /**
     * Get service-supported HTTP methods
     *
     * @return  Array   Headers array
     */
    final public function getSupportedMethods() {

        return $this->supported_http_methods;

    }

    /**
     * Get service-implemented HTTP methods
     *
     * @return  Array   Headers array
     */
    final public function getImplementedMethods() {

        if ( method_exists($this, 'any') ) return explode(",",$this->supported_http_methods);

        $supported_methods = explode(',',$this->supported_http_methods);

        $implemented_methods = Array();

        foreach ( $supported_methods as $method ) {

            if ( method_exists($this, strtolower($method)) ) array_push($implemented_methods,$method);

        }

        return $implemented_methods;

    }

    /**
     * Return the callable class method that reflect the requested one
     *
     * @return  Array   Headers array
     */
    final public function getCallableMethod($method) {

        if ( method_exists($this, strtolower($method)) ) return strtolower($method);

        else return "any";

    }

    /**
     * Get attributes and parameters that service expects
     *
     * @return  Array 
     */
    final public function getExpected($method) {

        $method = strtoupper($method);

        return Array(

            ( sizeof($this->expected_attributes[$method]) == 0 AND sizeof($this->expected_attributes["ANY"]) != 0 ) ? $this->expected_attributes["ANY"] : $this->expected_attributes[$method],

            ( sizeof($this->expected_parameters[$method]) == 0 AND sizeof($this->expected_parameters["ANY"]) != 0 ) ? $this->expected_parameters["ANY"] : $this->expected_parameters[$method]           

            );

    }

    /**
     * Get attributes and parameters that service likes
     *
     * @return  Array 
     */
    final public function getLiked($method) {

        $method = strtoupper($method);

        return Array(

            ( sizeof($this->liked_attributes[$method]) == 0 AND sizeof($this->liked_attributes["ANY"]) != 0 ) ? $this->liked_attributes["ANY"] : $this->liked_attributes[$method],

            ( sizeof($this->liked_parameters[$method]) == 0 AND sizeof($this->liked_parameters["ANY"]) != 0 ) ? $this->liked_parameters["ANY"] : $this->liked_parameters[$method]           

            );

    }

    /**
     * Get request attributes, populated by dispatcher
     *
     * @return  Array 
     */
    final public function getAttributes() {

        return $this->attributes;

    }

    /**
     * Get request parameters, populated by dispatcher
     *
     * @return  Array 
     */
    final public function getParameters($raw=false) {

        return $raw ? $this->raw_parameters : $this->parameters;
        
    }

}