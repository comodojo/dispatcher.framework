<?php namespace Comodojo\Dispatcher\Cache;

use \Comodojo\Dispatcher\Request\Model as Request;
use \Comodojo\Dispatcher\Router\Route;
use \Comodojo\Dispatcher\Response\Model as Response;

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


class ServerCache extends AbstractCache {

    // @NOTE: Server cache will not consider cacheable POST or PUT requests
    //        because of dispatcher internal structure: if post request is cached
    //        subsequent requests will never reach the service.
    private static $cachable_methods = array('GET', 'HEAD');

    private static $cachable_statuses = array(200, 203, 300, 301, 302, 404, 410);

    public function read(
        Request $request,
        Response $response
    ) {

        $name = md5( (string) $request->method . (string) $request->uri );

        $cache_object = $this->cache->setNamespace('dispatcherservice')->get($name);

        if ( is_null($cache_object) ) return false;

        $response->import($cache_object);

        return true;

    }

    public function dump(
        Request $request,
        Response $response,
        Route $route
    ) {

        $cache = strtoupper($route->getParameter('cache'));

        $ttl = $route->getParameter('ttl');

        $name = md5( (string) $request->method . (string) $request->uri );

        $method = $request->method->get();

        $status = $response->status->get();

        if (
            ( $cache == 'SERVER' || $cache == 'BOTH' ) &&
            in_array($method, self::$cachable_methods) &&
            in_array($status, self::$cachable_statuses)
        ){

            $this->cache
                ->setNamespace('dispatcherservice')
                ->set($name, $response->export(), $ttl === null ? self::DEFAULTTTL : intval($ttl));

        }

    }

}
