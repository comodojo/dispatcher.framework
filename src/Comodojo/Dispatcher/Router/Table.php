<?php namespace Comodojo\Dispatcher\Router;

use \Comodojo\Foundation\Base\Configuration;
use \Comodojo\Dispatcher\Components\AbstractModel;
use \Comodojo\Dispatcher\Cache\RouterCache;
use \Comodojo\Dispatcher\Request\Model as Request;
use \Comodojo\Dispatcher\Router\Parser;
use \Comodojo\Dispatcher\Router\Route;
use \Comodojo\Dispatcher\Router\Model as Router;
use \Comodojo\SimpleCache\Manager as SimpleCacheManager;
use \Comodojo\Exception\DispatcherException;
use \Psr\Log\LoggerInterface;
use \Countable;
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

class Table extends AbstractModel implements Countable {

    /**
     * Types of route that this table will accept
     * @var array
     */
    const ALLOWED_ROUTES = [ "ROUTE", "REDIRECT", "ERROR" ];

    /**
     * Current repository of routes
     * @var array
     */
    protected $routes = [];

    /**
     * @var Parser
     */
    protected $parser;

    /**
     * @var RouterCache
     */
    protected $cache;

    /**
     * Table constructor
     *
     * @param Configuration $configuration
     * @param LoggerInterface $logger
     * @param SimpleCacheManager $cache
     */
    public function __construct(
        Configuration $configuration,
        LoggerInterface $logger,
        SimpleCacheManager $cache
    ) {

        parent::__construct($configuration, $logger);

        $this->parser = new Parser($logger);
        $this->cache = new RouterCache($cache);

        $this->readCache();

    }

    public function add(
        $route,
        $type,
        $class = null,
        array $parameters = []
    ) {

        $type = strtoupper($type);

        if ( !in_array($type, self::ALLOWED_ROUTES) ) {

            $this->getLogger()->error("Invalid route definition - unknown type $type for route $route)");

        } else if ( $type == 'ROUTE' && empty($class) ) {

            $this->getLogger()->error("Invalid route definition - missing class for route $route)");

        } else {

            $routeData = $this->get($route);

            if ( !is_null($routeData) ) {

                $this->updateRoute($routeData, $type, $class, $parameters);

            } else {

                $folders = explode("/", $route);

                $this->registerRoute($folders, $type, $class, $parameters);

            }

        }

        return $this;

    }

    /**
     * Get registered routes count
     *
     * @return int
     */
    public function count() {

        return count($this->routes);

    }

    public function getRoutes() {

        return $this->routes;

    }

    public function get($route) {

        $regex = $this->regex($route);

        if (isset($this->routes[$regex])) {
            return $this->routes[$regex];
        }

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

    public function load(array $routes) {

        if (!empty($routes)) {

            foreach ($routes as $name => $route) {

                $this->add(
                    $route['route'],
                    $route['type'],
                    empty($route['class']) ? null : $route['class'],
                    empty($route['parameters']) ? [] : $route['parameters']
                );

            }

        }

        $count = $this->count();
        $this->logger->debug("$count routes loaded in routing table");

        $this->dumpCache();

    }

    // This method add a route to the supported list
    private function registerRoute($folders, $type, $class = null, array $parameters = []) {

        // The values associated with a route are as follows:
        // $route = new Route($this->router);
        $route = new Route();

        $this->updateRoute($route, $type, $class, $parameters);

        // $route->setType($type) // Type of route
        //     ->setClassName($class) // Class to be invoked
        //     ->setParameters($parameters); // Parameters passed via the composer.json configuration (cache, ttl, etc...)

        $this->logger->debug("Route table - route: ".implode("/", $folders));

        // This method generate a global regular expression which will be able to match all the URI supported by the route
        $regex = $this->parser->read($folders, $route);

        $this->logger->debug("Route table - route regex: $regex");

        $this->routes = array_merge($this->routes, [$regex => $route]);

    }

    private function updateRoute(Route $route, $type, $class = null, array $parameters = []) {

        $route->setType($type)
            ->setClassName($class)
            ->setParameters($parameters);

        if ( !empty($parameters['redirect-code']) ) $route->setRedirectCode($parameters['redirect-code']);
        if ( !empty($parameters['redirect-location']) ) $route->setRedirectLocation($parameters['redirect-location']);
        if ( !empty($parameters['redirect-message']) ) $route->setRedirectLocation($parameters['redirect-message']);
        if ( !empty($parameters['redirect-type']) ) $route->setRedirectType($parameters['redirect-type']);

        if ( !empty($parameters['error-code']) ) $route->setErrorCode($parameters['error-code']);
        if ( !empty($parameters['error-message']) ) $route->setErrorMessage($parameters['error-message']);

    }

    private function readCache() {

        if ($this->configuration->get('routing-table-cache') !== true) return;

        $data = $this->cache->read();

        if (is_null($data)) {

            $this->routes = [];

            return;

        }

        $this->routes = $data;

        $this->logger->debug("Routing table loaded from cache");

    }

    private function dumpCache() {

        if ($this->configuration->get('routing-table-cache') !== true) return;

        $ttl = $this->configuration->get('routing-table-ttl');

        if ($this->cache->dump($this->routes, $ttl)) {
            $this->logger->debug("Routing table saved to cache");
        } else {
            $this->logger->warning("Cannot save routing table to cache");
        }

    }

}
