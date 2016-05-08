<?php namespace Comodojo\Dispatcher\Router;

use \Comodojo\Dispatcher\Components\Model as DispatcherClassModel;
use \Comodojo\Dispatcher\Router\RoutingTable;
use \Comodojo\Dispatcher\Components\Timestamp as TimestampTrait;
use \Comodojo\Dispatcher\Request\Model as Request;
use \Comodojo\Dispatcher\Response\Model as Response;
use \Comodojo\Dispatcher\Extra\Model as Extra;
use \Comodojo\Dispatcher\Components\Configuration;
use \Comodojo\Cache\CacheManager;
use \Symfony\Component\Yaml\Yaml;
use \Monolog\Logger;
use \Comodojo\Exception\DispatcherException;
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

class Collector extends DispatcherClassModel {

    use TimestampTrait;

    private $bypass = false;

    private $classname = "";

    private $type = "";

    private $service = "";

    private $cache;

    private $request;

    private $response;

    private $table;

    public function __construct(
        Configuration $configuration,
        Logger $logger,
        CacheManager $cache,
        Extra $extra = null
    ) {

        parent::__construct($configuration, $logger);

        $this->table = new RoutingTable($logger);

        $this->cache = $cache;

        $this->extra = $extra;

        $this->setTimestamp();

    }

    public function getType() {

        return $this->type;

    }

    public function getService() {

        return $this->service;

    }

    public function getParameters() {

        return $this->parameters;

    }

    public function getClassName() {

        return $this->classname;

    }

    public function getInstance() {

        $class = $this->classname;

        if (class_exists($class)) {

            return new $class(
                $this->configuration,
                $this->logger,
                $this->request,
                $this,
                $this->response,
                $this->extra
            );

        }
        else return null;

    }

    public function add($route, $type, $class, $parameters = array()) {

        $routeData = $this->get($route);

        if (is_null($routeData)) {

            $this->table->put($route, $type, $class, $parameters);

        } else {

            $this->table->set($route, $type, $class, $parameters);

        }

    }

    public function get($route) {

        return $this->table->get($route);

    }

    public function remove($route) {

        return $this->table->remove($route);

    }

    public function bypass($mode = true) {

        $this->bypass = filter_var($mode, FILTER_VALIDATE_BOOLEAN);

        return $this;

    }

    public function route(Request $request) {

        $method = $request->method()->get();

        $methods = $this->configuration->get('allowed-http-methods');

        if ( ( $methods != null || !empty($methods) ) && in_array($method, $methods) === false ) {

            throw new DispatcherException("Method not allowed", 0, null, 405, array(
                "Allow" => implode(",",$methods)
            ));

        }

        $this->request = $request;

        if (!$this->parse()) {

            throw new DispatcherException("Unable to find a valid route for the specified uri", 0, null, 404);

        }

    }

    public function compose(Response $response) {

        $this->response = $response;

        $service = $this->getInstance();

        if (!is_null($service)) {

            $result;

            $method = $this->request->method()->get();

            $methods = $service->getImplementedMethods();

            if ( in_array($method, $metods) ) {

                $callable = $service->getMethod($method);

                try {

                    $result = call_user_func(array($service, $callable));

                } catch (DispatcherException $de) {

                    throw new DispatcherException(sprintf("Service '%s' exception for method '%s': %s", $this->service, $method, $de->getMessage()), 0, $de, 500);

                } catch (Exception $e) {

                    throw new DispatcherException(sprintf("Service '%s' execution failed for method '%s': %s", $this->service, $method, $e->getMessage()), 0, $e, 500);

                }

            } else {

                throw new DispatcherException(sprintf("Service '%s' doesn't implement method '%s'", $this->service, $method), 0, null, 501, array(
                    "Allow" => implode(",", $methods)
                ));

            }

            $this->response->content()->set($result);

        } else {

            throw new DispatcherException(sprintf("Unable to execute service '%s'", $this->service), 0, null, 500);

        }

    }

    public function loadFromYaml($yaml) {

        $routes = Yaml::parse($yaml);

        if ( !empty($routes) ) {

            foreach( $routes as $name => $route ) {

                $this->add($route['route'], $route['type'], $route['class'], $route['parameters']);

            }

        }

        return $this;

    }

    private function parse() {

        $path = $this->request->uri()->getPath();

        foreach ($this->table->routes() as $regex => $value) {

            if (preg_match("/" . $regex . "/", $path, $matches)) {

                $this->evalUri($value['query'], $matches);

                foreach ($value['parameters'] as $parameter => $value) {

                    $this->request->query()->set($parameter, $value);

                }

                $this->classname  = $value['class'];
                $this->type       = $value['type'];
                $this->service    = implode('.', $value['service']);
                $this->service    = empty($this->service)?"default":$this->service;

                return true;

            }

        }

        return false;

    }

    private function evalUri($parameters, $bits) {

        $count  = 0;

        foreach ($parameters as $key => $value) {

            if (isset($bits[$key])) {

                if (preg_match('/^' . $value['regex'] . '$/', $bits[$key], $matches)) {

                    if (count($matches) == 1) $matches = $matches[0];

                    $this->request->query()->set($key, $matches);

                }

            } elseif ($value['required']) {

                throw new DispatcherException(sprintf("Required parameter '%s' not specified.", $key), 1, null, 500);

            }

        }

    }

}
