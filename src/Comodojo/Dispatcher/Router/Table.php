<?php namespace Comodojo\Dispatcher\Router;

use \Comodojo\Dispatcher\Components\Model as DispatcherClassModel;
use \Comodojo\Dispatcher\Router\Parser;
use \Comodojo\Dispatcher\Router\Route;
use \Comodojo\Dispatcher\Router\Model as Router;
use \Monolog\Logger;
use \Comodojo\Dispatcher\Components\Configuration;
use \Comodojo\Cache\CacheManager;
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

class Table extends DispatcherClassModel {

    private $routes = array();

    private $parser;
    
    private $cache;

    private $router;

    public function __construct(
        CacheManager $cache,
        Router $router
    ) {

        parent::__construct($router->configuration(), $router->logger());

        $this->router = $router;

        $this->parser = new Parser($router);

        $this->cache = $cache;

        $this->readCache();

    }

    public function put($route, $type, $class, $parameters = array()) {

        $folders = explode("/", $route);

        $regex = $this->parser->read($folders);

        if (!isset($this->routes[$regex])) {

            $this->register($folders, $type, $class, $parameters);

        }

    }

    public function set($route, $type, $class, $parameters = array()) {

        $folders = explode("/", $route);

        $regex = $this->parser->read($folders);

        if (isset($this->routes[$regex])) {

            $this->register($folders, $type, $class, $parameters);

        }

    }

    public function add($route, $type, $class, $parameters = array()) {

        $routeData = $this->get($route);

        if (is_null($routeData)) {

            $this->put($route, $type, $class, $parameters);

        } else {

            $this->set($route, $type, $class, $parameters);

        }

    }

    public function get($route) {

        $folders = explode("/", $route);

        $regex = $this->parser->read($folders);

        if (isset($this->routes[$regex]))
            return $this->routes[$regex];
        else
            return null;

    }

    public function remove($route) {

        $folders = explode("/", $route);

        $regex = $this->parser->read($folders);

        if (isset($this->routes[$regex])) unset($this->routes[$regex]);

    }

    public function routes($routes = null) {

        if (is_null($routes)) {

            return $this->routes;

        } else {

            $this->routes = $routes;

            return $this;

        }

    }

    public function defaultRoute() {

        return $this->get('/');

    }

    public function load($routes) {

        if ( !empty($routes) ) {

            foreach( $routes as $name => $route ) {

                $this->add($route['route'], $route['type'], $route['class'], $route['parameters']);

            }

        }

        $this->logger->debug("Routing table loaded");

        return $this->dumpCache();

    }

    private function readCache() {

        $routes = $this->cache->get("dispatcher_routes");

        if (is_null($routes)) return null;

        foreach ($routes as $name => $data) {

            $route = new Route($this->router);

            $this->routes[$name] = $route->setData($data);

        }

        $this->logger->debug("Routing table loaded from cache");

        return $this;

    }

    private function dumpCache() {

        $routes = array();

        foreach($this->routes as $name => $route) {

            $routes[$name] = $route->getData();

        }

        $this->cache->set("dispatcher_routes", $routes, 24 * 60 * 60);

        $this->logger->debug("Routing table saved to cache");

        return $this;

    }

    // This method add a route to the supported list
    private function register($folders, $type, $class, $parameters) {

        // The values associated with a route are as follows:
        $route = new Route($this->router);
        $route->setType($type) // Type of route
            ->setClassName($class) // Class to be invoked
            ->setParameters($parameters); // Parameters passed via the composer.json configuration (cache, ttl, etc...)

        $this->logger->debug("ROUTE: " . implode("/", $folders));

        //$this->logger->debug("PARAMETERS: " . var_export($value, true));

        // This method generate a global regular expression which will be able to match all the URI supported by the route
        $regex = $this->parser->read($folders, $route);

        $this->logger->debug("ROUTE: " . $regex);

        //$this->logger->debug("PARAMETERS: " . var_export($value, true));

        $this->routes[$regex] = $route;

    }

}
