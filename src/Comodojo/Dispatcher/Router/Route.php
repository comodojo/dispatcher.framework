<?php namespace Comodojo\Dispatcher\Router;

use \Comodojo\Exception\DispatcherException;
use \Serializable;
use \InvalidArgumentException;
use \Exception;

/**
 * @package     Comodojo Dispatcher
 * @author      Marco Giovinazzi <marco.giovinazzi@comodojo.org>
 * @author      Marco Castiello <marco.castiello@gmail.com>
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

class Route implements Serializable {

    /**
     * @const string
     */
    const REDIRECT_REFRESH = 'REFRESH';

    /**
     * @const string
     */
    const REDIRECT_LOCATION = 'LOCATION';

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $classname;

    /**
     * @var int
     */
    protected $redirect_code;

    /**
     * @var string
     */
    protected $redirect_location;

    /**
     * @var string
     */
    protected $redirect_message;

    /**
     * @var string
     */
    protected $redirect_type = self::REDIRECT_LOCATION;

    /**
     * @var int
     */
    protected $error_code;

    /**
     * @var string
     */
    protected $error_message;

    /**
    * @var array
    */
    protected $parameters = [];

    /**
     * @var array
     */
    protected $service = [];

    /**
     * @var array
     */
    protected $request = [];

    /**
     * @var array
     */
    protected $query = [];

    public function getType() {

        return $this->type;

    }

    public function setType($type) {

        $this->type = $type;

        return $this;

    }

    public function getClassName() {

        return $this->classname;

    }

    public function setClassName($class) {

        $this->classname = $class;

        return $this;

    }

    public function getRedirectCode() {

        return $this->redirect_code;

    }

    public function setRedirectCode($code) {

        if ( $code < 300 || $code >= 400 ) {
            throw new InvalidArgumentException("Invalid redirection code $code");
        }

        $this->redirect_code = $code;

        return $this;

    }

    public function getRedirectLocation() {

        return $this->redirect_location;

    }

    public function setRedirectLocation($location) {

        $this->redirect_location = $location;

        return $this;

    }

    public function getRedirectMessage() {

        return $this->redirect_message;

    }

    public function setRedirectMessage($message) {

        $this->redirect_message = $message;

        return $this;

    }

    public function getRedirectType() {

        return $this->redirect_type;

    }

    public function setRedirectType($type) {

        if ( !in_array($type, [self::REDIRECT_REFRESH, self::REDIRECT_LOCATION]) ) {
            throw new InvalidArgumentException("Invalid redirection type $type");
        }

        $this->redirect_type = $type;

        return $this;

    }

    public function getErrorCode() {

        return $this->error_code;

    }

    public function setErrorCode($code) {

        if ( $code < 400 || $code >= 600 ) {
            throw new InvalidArgumentException("Invalid error code $code");
        }

        $this->error_code = $code;

        return $this;

    }

    public function getErrorMessage() {

        return $this->error_message;

    }

    public function setErrorMessage($message) {

        $this->error_message = $message;

        return $this;

    }

    public function getParameter($key) {

        $parameters = $this->parameters;

        return isset($parameters[$key]) ? $parameters[$key] : null;

    }

    public function getParameters() {

        return $this->parameters;

    }

    public function setParameter($key, $value) {

        $this->parameters = array_merge($this->parameters, array($key => $value));

        return $this;

    }

    public function setParameters($parameters) {

        $this->parameters = $parameters;

        return $this;

    }

    public function getRequestParameter($key) {

        $parameters = $this->request;

        return isset($parameters[$key]) ? $parameters[$key] : null;

    }

    public function getService() {

        return $this->service;

    }

    public function getServiceName() {

        return empty($this->service) ? "default" : implode('.', $this->service);

    }

    public function setService($service) {

        $this->service = $service;

        return $this;

    }

    public function addService($service) {

        $this->service = array_merge($this->service, array($service));

        return $this;

    }

    public function getRequestParameters() {

        return $this->request;

    }

    public function setRequestParameter($key, $value) {

        $this->request = array_merge($this->request, array($key => $value));

        return $this;

    }

    public function setRequestParameters($parameters) {

        $this->request = $parameters;

        return $this;

    }

    public function setQuery($key, $regex, $required = false) {

        $this->query = array_merge($this->query, [
            $key => [
                "regex" => $regex,
                "required" => $required
            ]
        ]);

        return $this;

    }

    public function isQueryRequired($key) {

        $query = $this->query;

        return isset($query[$key]) ? $query[$key]["required"] : false;

    }

    public function getQueryRegex($key) {

        $query = $this->query;

        return isset($query[$key]) ? $query[$key]["regex"] : null;

    }

    public function getQueries() {

        return $this->query;

    }

    public function setQueries($query) {

        $this->query = $query;

        return $this;

    }

    public function path($path) {

        // Because of the nature of the global regular expression, all the bits of the matched route are associated with a parameter key
        foreach ($this->query as $key => $value) {

            if ( isset($path[$key]) ) {
                /* if it's available a bit associated with the parameter name, it is compared against
                 * it's regular expression in order to extrect backreferences
                 */
                if ( preg_match('/^' . $value['regex'] . '$/', $path[$key], $matches) ) {

                    if ( count($matches) == 1 ) $matches = $matches[0]; // This is the case where no backreferences are present or available.

                    // The extracted value (with any backreference available) is added to the query parameters.
                    $this->setRequestParameter($key, $matches);

                }

            } elseif ($value['required']) {

                throw new DispatcherException(sprintf("Required parameter '%s' not specified.", $key), 1, null, 500);

            }

        }

        return $this;

    }

    /**
     * Return the serialized data
     *
     * @return string
     */
    public function serialize() {

        return serialize( (object) [
            'classname' => $this->classname,
            'type' => $this->type,
            'service' => $this->service,
            'parameters' => $this->parameters,
            'request' => $this->request,
            'query' => $this->query
        ]);

    }

    /**
     * Return the unserialized object
     *
     * @param string $data Serialized data
     *
     */
    public function unserialize($data) {

        $parts = unserialize($data);

        $this->classname = $parts->classname;
        $this->type = $parts->type;
        $this->service = $parts->service;
        $this->parameters = $parts->parameters;
        $this->request = $parts->request;
        $this->query = $parts->query;

    }

}
