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

/**
 *
 * @package     Comodojo dispatcher
 * @author      Marco Giovinazzi <marco.giovinazzi@comodojo.org>
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
        Emitter $events = null,
        CacheManager $cache = null,
        Logger $logger = null
    ) {

        ob_start();

        $this->setTimestamp();

        $this->configuration = new Configuration($configuration);
        
        $this->events = is_null($events) ? new Emitter() : $emitter;
        
        $this->logger = is_null($logger) ? DispatcherLogger::create($this->configuration) : $logger;
        
        $this->cache = is_null($cache) ? DispatcherCache::create($this->configuration, $this->logger) : $cache;
        
        $this->request = new Request($this->configuration, $this->logger);
        
        $this->router = new RouteCollector($this->configuration, $this->logger, $this->cache);
        
        $this->response = new Response($this->configuration, $this->logger);
        
        $this->extra = new Extra($this->logger);
        
        $this->router->load( $routing_table );

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
            
        } else {
            
            $this->events->emit( $this->emitServiceSpecializedEvents('dispatcher.request') );

            $this->events->emit( $this->emitServiceSpecializedEvents('dispatcher.request.'.$this->request->method()->get()) );
    
            $this->events->emit( $this->emitServiceSpecializedEvents('dispatcher.request.#') );
    
            $this->router->route($this->request);
    
            $this->events->emit( $this->emitServiceSpecializedEvents('dispatcher.route') );
    
            $this->events->emit( $this->emitServiceSpecializedEvents('dispatcher.route.'.$this->router->getType()) );
    
            $this->events->emit( $this->emitServiceSpecializedEvents('dispatcher.route.'.$this->router->getService()) );
    
            $this->events->emit( $this->emitServiceSpecializedEvents('dispatcher.route.#') );
    
            // translate route to service
    
            $this->router->compose($this->response);
    
            $this->events->emit( $this->emitServiceSpecializedEvents('dispatcher.response') );
    
            $this->events->emit( $this->emitServiceSpecializedEvents('dispatcher.response.'.$this->response->status()->get()) );
    
            $this->events->emit( $this->emitServiceSpecializedEvents('dispatcher.response.#') );
            
        }

        $return = Processor::parse($this->configuration, $this->logger, $this->response);

        ob_end_clean();

        return $return;

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

}
