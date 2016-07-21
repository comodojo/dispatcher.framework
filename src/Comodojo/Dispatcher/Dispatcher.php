<?php namespace Comodojo\Dispatcher;

use \Psr\Log\LoggerInterface;
use \Comodojo\Dispatcher\Components\Configuration;
use \Comodojo\Dispatcher\Components\DefaultConfiguration;
use \Comodojo\Dispatcher\Components\EventsManager;
use \Comodojo\Dispatcher\Components\Timestamp as TimestampTrait;
use \Comodojo\Dispatcher\Components\CacheManager as DispatcherCache;
use \Comodojo\Dispatcher\Components\LogManager;
use \Comodojo\Dispatcher\Request\Model as Request;
use \Comodojo\Dispatcher\Router\Model as Router;
use \Comodojo\Dispatcher\Response\Model as Response;
use \Comodojo\Dispatcher\Extra\Model as Extra;
use \Comodojo\Dispatcher\Output\Processor;
use \Comodojo\Dispatcher\Events\DispatcherEvent;
use \Comodojo\Dispatcher\Events\ServiceEvent;
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
        EventsManager $events = null,
        CacheManager $cache = null,
        LoggerInterface $logger = null
    ) {

        // starting output buffer
        ob_start();

        // fix current timestamp
        $this->setTimestamp();

        // parsing configuration
        $this->configuration = new Configuration( DefaultConfiguration::get() );

        $this->configuration()->merge($configuration);

        // init core components
        $this->logger = is_null($logger) ? LogManager::create($this->configuration()) : $logger;

        $this->events = is_null($events) ? new EventsManager($this->logger()) : $events;

        $this->cache = is_null($cache) ? DispatcherCache::create($this->configuration(), $this->logger()) : $cache;

        // init models
        $this->extra = new Extra($this->logger());
        
        $this->request = new Request($this->configuration(), $this->logger());

        $this->router = new Router($this->configuration(), $this->logger(), $this->cache(), $this->extra());

        $this->response = new Response($this->configuration(), $this->logger());

        // we're ready!
        $this->logger()->debug("Dispatcher ready, current date ".date('c', $this->getTimestamp()));

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

    public function logger() {

        return $this->logger;

    }

    public function dispatch() {

        $this->logger()->debug("Starting to dispatch.");

        $this->logger()->debug("Emitting global dispatcher event.");

        $this->events()->emit( new DispatcherEvent($this) );

        if ( $this->configuration()->get('enabled') === false ) {

            $this->logger()->debug("Dispatcher disabled, shutting down gracefully.");

            $status = $this->configuration()->get('disabled-status');

            $content = $this->configuration()->get('disabled-message');

            $this->response()->status()->set($status);

            $this->response()->content()->set($content);

            return $this->shutdown();

        }

        $this->events()->emit( $this->emitServiceSpecializedEvents('dispatcher.request') );

        $this->events()->emit( $this->emitServiceSpecializedEvents('dispatcher.request.'.$this->request()->method()->get()) );

        $this->events()->emit( $this->emitServiceSpecializedEvents('dispatcher.request.#') );

        if ( $this->readCache() ) {
            
            return $this->shutdown();
            
        }

        $this->logger()->debug("Starting router.");

        try {

            $route = $this->router()->route($this->request());

        } catch (DispatcherException $de) {

            $this->logger()->debug("Route error (".$de->getStatus()."), shutting down dispatcher.");

            $this->processDispatcherException($de);

            return $this->shutdown();

        }

        $route_type = $route->getType();

        $route_service = $route->getServiceName();

        $this->logger()->debug("Route acquired, type $route_type directed to $route_service.");

        $this->events()->emit( $this->emitServiceSpecializedEvents('dispatcher.route') );

        $this->events()->emit( $this->emitServiceSpecializedEvents('dispatcher.route.'.$route_type) );

        $this->events()->emit( $this->emitServiceSpecializedEvents('dispatcher.route.'.$route_service) );

        $this->events()->emit( $this->emitServiceSpecializedEvents('dispatcher.route.#') );

        // translate route to service

        $this->logger()->debug("Running $route_service service.");

        try {

            $this->router()->compose($this->response());

        } catch (DispatcherException $de) {

            $this->logger()->debug("Service exception (".$de->getStatus()."), shutting down dispatcher.");

            $this->processDispatcherException($de);

        }
        
        $this->processConfigurationParameters($route);
        
        $this->dumpCache($route);

        return $this->shutdown();

    }

    private function readCache() {
        
        $name = (string) $this->request()->uri();
        
        $cache = $this->cache()->setNamespace('dispatcher-service')->get($name);
        
        if ( is_null($cache) ) return false;
        
        $this->response = $cache;
        
        return true;
        
    }
    
    private function dumpCache($route) {
        
        $cache = strtoupper($route->getParameter('cache'));
        $ttl = $route->getParameter('ttl');
        $name = (string) $this->request()->uri();
        
        if ( $cache == 'CLIENT' || $cache == 'BOTH' ) {
            
            if ( $ttl > 0 ) {
                
                $this->response()->headers()->set("Cache-Control","max-age=".$ttl.", must-revalidate");
                $this->response()->headers()->set("Expires",gmdate("D, d M Y H:i:s", (int)$this->request()->getTimestamp() + $ttl)." GMT");
                
            } else {
                
                $this->response()->headers()->set("Cache-Control","no-cache, must-revalidate");
                $this->response()->headers()->set("Expires","Mon, 26 Jul 1997 05:00:00 GMT");
                
            }
            
        }
        
        if ( $cache == 'SERVER' || $cache == 'BOTH' ) {
            
            $this->cache()->setNamespace('dispatcher-service')->set($name, $this->response(), $ttl);
            
        }
       
    }
    
    private function processConfigurationParameters($route) {
        
        $params = $route->getParameter('headers');
        
        if ( !empty($params) && is_array($params) ) {
            
            foreach($params as $name=>$value) $this->response()->headers()->set($name, $value);
        }
        
    }

    private function emitServiceSpecializedEvents($name) {

        $this->logger()->debug("Emitting $name service-event.");

        return new ServiceEvent(
            $name,
            $this->logger(),
            $this->request(),
            $this->router(),
            $this->response(),
            $this->extra()
        );

    }

    private function processDispatcherException(DispatcherException $de) {

        $status = $de->getStatus();

        $message = $de->getMessage();

        //$headers = $de->getHeaders();
        $headers = array();

        $this->response()->status()->set($status);

        $this->response()->content()->set($message);

        $this->response()->headers()->merge($headers);

    }

    private function shutdown() {

        $this->events()->emit( $this->emitServiceSpecializedEvents('dispatcher.response') );

        $this->events()->emit( $this->emitServiceSpecializedEvents('dispatcher.response.'.$this->response()->status()->get()) );

        $this->events()->emit( $this->emitServiceSpecializedEvents('dispatcher.response.#') );

        $this->logger()->debug("Composing return value.");

        $return = Processor::parse($this->configuration(), $this->logger(), $this->response());

        $this->logger()->debug("Dispatcher run-cycle ends.");

        ob_end_clean();

        return $return;

    }

}
