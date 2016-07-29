<?php namespace Comodojo\Dispatcher\Router;

use \Comodojo\Dispatcher\Components\Model as DispatcherClassModel;
use \Comodojo\Dispatcher\Router\Parser;
use \Comodojo\Dispatcher\Router\Route;
use \Comodojo\Dispatcher\Router\Model as Router;
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

    public function __construct(
        CacheManager $cache,
        Router $router
    ) {

        parent::__construct($router->configuration, $router->logger);

        $this->routes = array();

        $this->router = $router;

        $this->parser = new Parser($this->logger);

        $this->cache = $cache;

        $this->readCache();

    }

    public function add($route, $type, $class, $parameters = array()) {

        $routeData = $this->get($route);

        if (!is_null($routeData)) {

            $routeData->setType($type)
                ->setClassName($class)
                ->setParameters($parameters);

        } else {

            $folders = explode("/", $route);

            $this->register($folders, $type, $class, $parameters);

        }

        return $this;

    }

    public function get($route) {

        $regex = $this->regex($route);

        if (isset($this->routes[$regex]))
            return $this->routes[$regex];
        else
            return null;

    }

    public function regex($route) {

        $folders = explode("/", $route);

        return $this->parser->read($folders);

    }

    public function remove($route) {

        $regex = $this->regex($route);

        $routes = $this->routes;

        if (isset($routes[$regex])) {

            unset($routes[$regex]);

            $this->routes = $routes;

            return true;

        }

        return false;

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

        $this->dumpCache();

    }

    private function readCache() {

        if ( $this->configuration->get('routing-table-cache') !== true ) return;

        $this->routes = $this->cache->setNamespace('dispatcherinternals')->get("dispatcher-routes");

        if (is_null($routes)) return;

        $this->logger->debug("Routing table loaded from cache");

    }

    private function dumpCache() {

        if ( $this->configuration->get('routing-table-cache') !== true ) return;

        $ttl = $this->configuration->get('routing-table-ttl');

        $this->cache->setNamespace('dispatcherinternals')->set("dispatcher-routes", $this->routes, $ttl == null ? 86400 : intval($ttl));

        $this->logger->debug("Routing table saved to cache");

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

        $this->routes = array_merge($this->routes, array(
            $regex => $route
        ));

    }

}
