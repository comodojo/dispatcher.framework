<?php namespace Comodojo\Dispatcher;

use \Monolog\Logger;
use \Comodojo\Dispatcher\Components\Configuration;
use \Comodojo\Dispatcher\Request\Model as Request;
use \Comodojo\Dispatcher\Router\Collector as RouteCollector;
use \Comodojo\Dispatcher\Response\Model as Response;
use \Comodojo\Dispatcher\Extra\Model as Extra;
use \Comodojo\Dispatcher\Components\Timestamp as TimestampTrait;
use \Comodojo\Dispatcher\Output\Processor;
use \Comodojo\Dispatcher\Events\DispatcherEvent;
use \Comodojo\Dispatcher\Events\ServiceEvent;
use \Comodojo\Dispatcher\Router\RoutingTableInterface;
use \Comodojo\Dispatcher\Log\DispatcherLogger;
use \Comodojo\Dispatcher\Cache\DispatcherCache;
use \Comodojo\Cache\CacheManager;
use \League\Event\Emitter;
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

class Dispatcher {

    use TimestampTrait;

    private $configuration;

    private $request;

    private $router;

    private $response;

    private $extra;

    private $logger;

    private $cache;

    private $events;

    public function __construct(
        $configuration = array(),
        Emitter $emitter = null,
        CacheManager $cache = null,
        Logger $logger = null
    ) {

        ob_start();

        $this->setTimestamp();

        $this->extra = new Extra($this->logger);

        $this->configuration = new Configuration($this->getDefaultConfiguration());

        $this->configuration->merge($configuration);

        $this->events = is_null($emitter) ? new Emitter() : $emitter;

        $this->logger = is_null($logger) ? DispatcherLogger::create($this->configuration) : $logger;

        $this->cache = is_null($cache) ? DispatcherCache::create($this->configuration, $this->logger) : $cache;

        $this->request = new Request($this->configuration, $this->logger);

        $this->router = new RouteCollector($this->configuration, $this->logger, $this->cache, $this->extra);

        $this->response = new Response($this->configuration, $this->logger);

    }

    public function configuration() {

        return $this->configuration;

    }

    public function events() {

        return $this->events;

    }

    public function cache() {

        return $this->cache;

    }

    public function request() {

        return $this->request;

    }

    public function router() {

        return $this->router;

    }

    public function response() {

        return $this->response;

    }

    public function extra() {

        return $this->extra;

    }

    public function dispatch() {

        $this->events->emit( new DispatcherEvent($this) );

        if ( $this->configuration()->get('dispatcher-enabled') === false ) {

            $status = $this->configuration()->get('dispatcher-disabled-status');

            $content = $this->configuration()->get('dispatcher-disabled-message');

            $this->response()->status()->set($status);

            $this->response()->content()->set($content);

            return $this->shutdown();

        }

        $this->events->emit( $this->emitServiceSpecializedEvents('dispatcher.request') );

        $this->events->emit( $this->emitServiceSpecializedEvents('dispatcher.request.'.$this->request->method()->get()) );

        $this->events->emit( $this->emitServiceSpecializedEvents('dispatcher.request.#') );

        try {

            $this->router->route($this->request);

        } catch (DispatcherException $de) {

            $this->response()->status()->set( $de->getStatus() );

            $this->response()->content()->set( $de->getMessage() );

            return $this->shutdown();

        }

        $this->events->emit( $this->emitServiceSpecializedEvents('dispatcher.route') );

        $this->events->emit( $this->emitServiceSpecializedEvents('dispatcher.route.'.$this->router->getType()) );

        $this->events->emit( $this->emitServiceSpecializedEvents('dispatcher.route.'.$this->router->getService()) );

        $this->events->emit( $this->emitServiceSpecializedEvents('dispatcher.route.#') );

        // translate route to service

        try {

            $this->router->compose($this->response);

        } catch (DispatcherException $de) {

            $this->response()->status()->set( $de->getStatus() );

            $this->response()->content()->set( $de->getMessage() );

        }

        return $this->shutdown();

    }

    private function emitServiceSpecializedEvents($name) {

        return new ServiceEvent(
            $name,
            $this->logger,
            $this->request,
            $this->router,
            $this->response,
            $this->extra
        );

    }

    private function shutdown() {

        $this->events->emit( $this->emitServiceSpecializedEvents('dispatcher.response') );

        $this->events->emit( $this->emitServiceSpecializedEvents('dispatcher.response.'.$this->response->status()->get()) );

        $this->events->emit( $this->emitServiceSpecializedEvents('dispatcher.response.#') );

        $return = Processor::parse($this->configuration, $this->logger, $this->response);

        ob_end_clean();

        return $return;

    }

    private function getDefaultConfiguration() {

        return array(
            'dispatcher-enabled' => true,
            'dispatcher-disabled-status' => 503,
            'dispatcher-disabled-message' => 'Dispatcher offline',
            'dispatcher-log-name' => 'dispatcher',
            'dispatcher-log-enabled' => false,
            'dispatcher-log-level' => 'INFO',
            'dispatcher-log-target' => '%dispatcher-log-folder%/dispatcher.log',
            'dispatcher-log-folder' => '/log',
            'dispatcher-supported-methods' => array('GET','PUT','POST','DELETE','OPTIONS','HEAD'),
            'dispatcher-default-encoding' => 'UTF-8',
            'dispatcher-cache-enabled' => true,
            'dispatcher-cache-ttl' => 3600,
            'dispatcher-cache-folder' => '/cache',
            'dispatcher-cache-algorithm'  => 'PICK_FIRST',
            // should we implement this?
            //'dispatcher-autoroute' => false,
            'dispatcher-base-url' => self::urlGetAbsolute(),
            'dispatcher-real-path' => self::pathGetAbsolute()
        );

    }

    private static function urlGetAbsolute() {

        $http = 'http' . ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 's' : '') . '://';

        $uri = preg_replace("/\/index.php(.*?)$/i", "", $_SERVER['PHP_SELF']);

        return ( $http . $_SERVER['HTTP_HOST'] . $uri . "/" );

    }

    private static function pathGetAbsolute() {

        return realpath(dirname(__FILE__)."/../../../../")."/";

    }

}
