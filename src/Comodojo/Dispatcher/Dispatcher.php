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

    // protected $mode = self::PROTECTDATA;

    /**
     * The main dispatcher constructor.
     *
     * @property Configuration $configuration
     * @property LoggerInterface $logger
     * @property EventsManager $events
     * @property Cache $cache
     * @property Extra $extra
     * @property Request $request
     * @property Router $router
     * @property Response $response
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

        $this->setRaw('events', is_null($events) ? EventsManager::create($this->logger) : $events);
        $this->setRaw('cache', is_null($cache) ? SimpleCacheManager::createFromConfiguration($this->configuration, $this->logger) : $cache);

        // init models
        $this->setRaw('extra', new Extra($this->logger));
        $this->setRaw('request', new Request($this->configuration, $this->logger));
        $this->setRaw('router', new Router($this->configuration, $this->logger, $this->cache, $this->extra));
        $this->setRaw('response', new Response($this->configuration, $this->logger));

        // we're ready!
        $this->logger->debug("Dispatcher ready");

    }

    public function dispatch() {

        $this->logger->debug("Starting to dispatch.");

        $this->logger->debug("Emitting global dispatcher event.");

        $this->events->emit( new DispatcherEvent($this) );

        if ( $this->configuration->get('enabled') === false ) {

            $this->logger->debug("Dispatcher disabled, shutting down gracefully.");

            $status = $this->configuration->get('disabled-status');

            $content = $this->configuration->get('disabled-message');

            $this->response->status->set($status);

            $this->response->content->set($content);

            return $this->shutdown();

        }

        $cache = new ServerCache($this->cache);

        $this->events->emit( $this->emitServiceSpecializedEvents('dispatcher.request') );

        $this->events->emit( $this->emitServiceSpecializedEvents('dispatcher.request.'.$this->request->method->get()) );

        $this->events->emit( $this->emitServiceSpecializedEvents('dispatcher.request.#') );

        if ( $cache->read($this->request, $this->response) ) {

            return $this->shutdown();

        }

        $this->logger->debug("Starting router");

        try {

            $this->route = $this->router->route($this->request);

        } catch (DispatcherException $de) {

            $this->logger->debug("Route error (".$de->getStatus()."), shutting down dispatcher");

            $this->processDispatcherException($de);

            return $this->shutdown();

        }

        $route_type = $this->route->getType();

        $route_service = $this->route->getServiceName();

        $this->logger->debug("Route acquired, type $route_type directed to $route_service");

        $this->events->emit( $this->emitServiceSpecializedEvents('dispatcher.route') );

        $this->events->emit( $this->emitServiceSpecializedEvents('dispatcher.route.'.$route_type) );

        $this->events->emit( $this->emitServiceSpecializedEvents('dispatcher.route.'.$route_service) );

        $this->events->emit( $this->emitServiceSpecializedEvents('dispatcher.route.#') );

        // translate route to service

        $this->logger->debug("Running $route_service service");

        try {

            $this->router->compose($this->response);

        } catch (DispatcherException $de) {

            $this->logger->debug("Service exception (".$de->getStatus()."), shutting down dispatcher");

            $this->processDispatcherException($de);

        }

        $this->processConfigurationParameters($this->route);

        $cache->dump($this->request, $this->response, $this->route);

        return $this->shutdown();

    }

    private function processConfigurationParameters($route) {

        $params = $route->getParameter('headers');

        if ( !empty($params) && is_array($params) ) {

            foreach($params as $name=>$value) $this->response->headers->set($name, $value);
        }

    }

    private function emitServiceSpecializedEvents($name) {

        $this->logger->debug("Emitting $name service-event");

        return new ServiceEvent(
            $name,
            $this->logger,
            $this->request,
            $this->router,
            $this->response,
            $this->extra
        );

    }

    private function processDispatcherException(DispatcherException $de) {

        $status = $de->getStatus();

        $message = $de->getMessage();

        $headers = $de->getHeaders();

        $this->response->status->set($status);

        $this->response->content->set($message);

        $this->response->headers->merge($headers);

    }

    private function shutdown() {

        $this->response->consolidate($this->request, $this->route);

        $this->events->emit( $this->emitServiceSpecializedEvents('dispatcher.response') );

        $this->events->emit( $this->emitServiceSpecializedEvents('dispatcher.response.'.$this->response->status->get()) );

        $this->events->emit( $this->emitServiceSpecializedEvents('dispatcher.response.#') );

        $this->logger->debug("Composing return value");

        $return = Processor::parse($this->configuration, $this->logger, $this->request, $this->response);

        $this->logger->debug("Dispatcher run-cycle ends");

        // This could cause WSOD with some PHP-FPM configurations
        // if ( function_exists('fastcgi_finish_request') ) fastcgi_finish_request();
        // else ob_end_clean();
        ob_end_clean();

        return $return;

    }

}
