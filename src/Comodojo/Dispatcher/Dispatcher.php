<?php namespace Comodojo\Dispatcher;

use \Comodojo\Dispatcher\Components\AbstractModel;
use \Comodojo\Dispatcher\Components\DefaultConfiguration;
use \Comodojo\Dispatcher\Cache\ServerCache;
use \Comodojo\Dispatcher\Request\Model as Request;
use \Comodojo\Dispatcher\Router\Model as Router;
use \Comodojo\Dispatcher\Response\Model as Response;
use \Comodojo\Dispatcher\Extra\Model as Extra;
use \Comodojo\Dispatcher\Output\Processor;
use \Comodojo\Dispatcher\Events\DispatcherEvent;
use \Comodojo\Dispatcher\Events\ServiceEvent;
use \Comodojo\Dispatcher\Traits\CacheTrait;
use \Comodojo\Dispatcher\Traits\RequestTrait;
use \Comodojo\Dispatcher\Traits\ResponseTrait;
use \Comodojo\Dispatcher\Traits\RouterTrait;
use \Comodojo\Dispatcher\Traits\ExtraTrait;
use \Comodojo\Dispatcher\Traits\EventsTrait;
use \Comodojo\Foundation\Base\Configuration;
use \Comodojo\Foundation\Timing\TimingTrait;
use \Comodojo\Foundation\Events\Manager as EventsManager;
use \Comodojo\Foundation\Logging\Manager as LogManager;
use \Comodojo\SimpleCache\Manager as SimpleCacheManager;
use \Psr\Log\LoggerInterface;
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

class Dispatcher extends AbstractModel {

    use TimingTrait;
    use CacheTrait;
    use RequestTrait;
    use ResponseTrait;
    use RouterTrait;
    use ExtraTrait;
    use EventsTrait;

    protected $route;

    /**
     * The main dispatcher constructor.
     *
     */
    public function __construct(
        array $configuration = [],
        EventsManager $events = null,
        SimpleCacheManager $cache = null,
        LoggerInterface $logger = null
    ) {

        // starting output buffer
        ob_start();

        // fix current timestamp
        $this->setTiming();

        // init core components
        // create new configuration object and merge configuration
        $configuration_object = new Configuration( DefaultConfiguration::get() );
        $configuration_object->merge($configuration);

        $logger = is_null($logger) ? LogManager::createFromConfiguration($configuration_object)->getLogger() : $logger;

        parent::__construct($configuration_object, $logger);

        $this->logger->debug("--------------------------------------------------------");
        $this->logger->debug("Dispatcher run-cycle starts at ".$this->getTime()->format('c'));

        try {

            // init other components
            $this->setEvents(is_null($events) ? EventsManager::create($this->logger) : $events);
            $this->setCache(is_null($cache) ? SimpleCacheManager::createFromConfiguration($this->configuration, $this->logger) : $cache);

            // init models
            $this->setExtra(new Extra($this->logger));
            $this->setRequest(new Request($this->configuration, $this->logger));
            $this->setRouter(new Router($this->configuration, $this->logger, $this->cache, $this->events, $this->extra));
            $this->setResponse(new Response($this->configuration, $this->logger));

        } catch (Exception $e) {

            $this->logger->critical($e->getMessage(),$e->getTrace());

            throw $e;

        }

        // we're ready!
        $this->logger->debug("Dispatcher ready");

    }

    public function dispatch() {

        $logger = $this->getLogger();
        $configuration = $this->getConfiguration();
        $events = $this->getEvents();

        $logger->debug("Starting to dispatch.");

        $logger->debug("Emitting global dispatcher event.");
        $events->emit( new DispatcherEvent($this) );

        if ( $configuration->get('enabled') === false ) {

            $logger->debug("Dispatcher disabled, shutting down gracefully.");

            $status = $configuration->get('disabled-status');
            $content = $configuration->get('disabled-message');

            $this->getResponse()->getStatus()->set($status);
            $this->getResponse()->getContent()->set($content);

            return $this->shutdown();

        }

        $cache = new ServerCache($this->getCache());

        $events->emit( $this->createServiceSpecializedEvents('dispatcher.request') );
        $events->emit( $this->createServiceSpecializedEvents('dispatcher.request.'.$this->request->method->get()) );
        $events->emit( $this->createServiceSpecializedEvents('dispatcher.request.#') );

        if ( $cache->read($this->request, $this->response) ) {
            // we have a cache!
            // shutdown immediately
            return $this->shutdown();
        }

        $logger->debug("Starting router");

        try {

            $this->route = $this->getRouter()->route($this->request);

        } catch (DispatcherException $de) {

            $logger->debug("Route error (".$de->getStatus()."), shutting down dispatcher");
            $this->processDispatcherException($de);
            return $this->shutdown();

        }

        $route_type = $this->route->getType();

        $route_service = $this->route->getServiceName();

        $logger->debug("Route acquired, type $route_type directed to $route_service");

        $events->emit( $this->createServiceSpecializedEvents('dispatcher.route') );
        $events->emit( $this->createServiceSpecializedEvents('dispatcher.route.'.$route_type) );
        $events->emit( $this->createServiceSpecializedEvents('dispatcher.route.'.$route_service) );
        $events->emit( $this->createServiceSpecializedEvents('dispatcher.route.#') );

        // translate route to service
        $logger->debug("Running $route_service service");

        try {

            $this->router->compose($this->response);

        } catch (DispatcherException $de) {

            $logger->debug("Service exception (".$de->getStatus()."), shutting down dispatcher");
            $this->processDispatcherException($de);

        }

        $this->processConfigurationParameters($this->route);

        $cache->dump($this->request, $this->response, $this->route);

        return $this->shutdown();

    }

    private function processConfigurationParameters($route) {

        $params = $route->getParameter('headers');

        if ( !empty($params) && is_array($params) ) {
            foreach($params as $name => $value) $this->getResponse()->getHeaders()->set($name, $value);
        }

    }

    private function createServiceSpecializedEvents($name) {

        $this->logger->debug("Emitting $name service-event");

        return new ServiceEvent(
            $name,
            $this->getConfiguration(),
            $this->getLogger(),
            $this->getCache(),
            $this->getEvents(),
            $this->getRequest(),
            $this->getRouter(),
            $this->getResponse(),
            $this->getExtra()
        );

    }

    private function processDispatcherException(DispatcherException $de) {

        $status = $de->getStatus();
        $message = $de->getMessage();
        $headers = $de->getHeaders();

        $response = $this->getResponse();

        $response->getStatus()->set($status);
        $response->getContent()->set($message);
        $response->getHeaders()->merge($headers);

    }

    private function shutdown() {

        $events = $this->getEvents();
        $request = $this->getRequest();
        $response = $this->getResponse();

        $response->consolidate($request, $this->route);

        $events->emit( $this->createServiceSpecializedEvents('dispatcher.response') );
        $events->emit( $this->createServiceSpecializedEvents('dispatcher.response.' . $response->getStatus()->get()) );
        $events->emit( $this->createServiceSpecializedEvents('dispatcher.response.#') );

        $this->logger->debug("Composing return value");

        $return = Processor::parse($this->getConfiguration(), $this->logger, $request, $response);

        $this->logger->debug("Dispatcher run-cycle ends");

        // This could cause WSOD with some PHP-FPM configurations
        // if ( function_exists('fastcgi_finish_request') ) fastcgi_finish_request();
        // else ob_end_clean();
        ob_end_clean();

        return $return;

    }

}
