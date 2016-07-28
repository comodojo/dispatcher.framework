<?php namespace Comodojo\Dispatcher\Router;

use \Comodojo\Dispatcher\Components\DataAccess as DataAccessTrait;
use \Comodojo\Dispatcher\Components\DataSerialization as DataSerializationTrait;
use \Comodojo\Exception\DispatcherException;
use \Serializable;
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
    
    use DataAccessTrait;
    
    use DataSerializationTrait;

    public function __construct() {

        $this->classname = "";

        $this->type = "";

        $this->service = array();

        $this->parameters = array();

        $this->query = array();

    }

    public function getType() {

        return $this->type;

    }

    public function setType($type) {

        $this->type = $type;

        return $this;

    }

    public function getService() {

        return $this->service;

    }

    public function getServiceName() {

        return (empty($this->service))?"default":implode('.', $this->service);

    }

    public function setService($service) {

        $this->service = $service;

        return $this;

    }

    public function addService($service) {

        array_push($this->service, $service);

        return $this;

    }

    public function getParameter($key) {

        return (isset($this->parameters[$key]))?$this->parameters[$key]:null;

    }

    public function getParameters() {

        return $this->parameters;

    }

    public function setParameter($key, $value) {

        $this->parameters[$key] = $value;

        return $this;

    }

    public function setParameters($parameters) {

        $this->parameters = $parameters;

        return $this;

    }

    public function setQuery($key, $regex, $required = false) {

        $this->query[$key] = array(
            "regex" => $regex,
            "required" => $required
        );

        return $this;

    }

    public function isQueryRequired($key) {

        return isset($this->query[$key])?$this->query[$key]["required"]:false;

    }

    public function getQueryRegex($key) {

        return isset($this->query[$key])?$this->query[$key]["regex"]:null;

    }

    public function getQueries() {

        return $this->query;

    }

    public function setQueries($query) {

        $this->query = $query;

        return $this;

    }

    public function getClassName() {

        return $this->classname;

    }

    public function setClassName($class) {

        $this->classname = $class;

        return $this;

    }

    public function path($path) {

        // Because of the nature of the global regular expression, all the bits of the matched route are associated with a parameter key
        foreach ($this->query as $key => $value) {

            if (isset($path[$key])) {
                /* if it's available a bit associated with the parameter name, it is compared against
                 * it's regular expression in order to extrect backreferences
                 */
                if (preg_match('/^' . $value['regex'] . '$/', $path[$key], $matches)) {

                    if (count($matches) == 1) $matches = $matches[0]; // This is the case where no backreferences are present or available.

                    // The extracted value (with any backreference available) is added to the query parameters.
                    $this->setParameter($key, $matches);

                }

            } elseif ($value['required']) {

                throw new DispatcherException(sprintf("Required parameter '%s' not specified.", $key), 1, null, 500);

            }

        }

        return $this;

    }

}
