<?php namespace Comodojo\Dispatcher;

use \Comodojo\Dispatcher\Components\AbstractModel;
use \Comodojo\Dispatcher\Components\DefaultConfiguration;
use \Comodojo\Dispatcher\Cache\ServerCache;
use \Comodojo\Dispatcher\Request\Model as Request;
use \Comodojo\Dispatcher\Router\Model as Router;
use \Comodojo\Dispatcher\Router\Route;
use \Comodojo\Dispatcher\Response\Model as Response;
use \Comodojo\Dispatcher\Extra\Model as Extra;
use \Comodojo\Dispatcher\Output\Processor;
use \Comodojo\Dispatcher\Output\Redirect;
use \Comodojo\Dispatcher\Output\Error;
use \Comodojo\Dispatcher\Events\DispatcherEvent;
use \Comodojo\Dispatcher\Events\ServiceEvent;
use \Comodojo\Dispatcher\Traits\CacheTrait;
use \Comodojo\Dispatcher\Traits\ServerCacheTrait;
use \Comodojo\Dispatcher\Traits\RequestTrait;
use \Comodojo\Dispatcher\Traits\ResponseTrait;
use \Comodojo\Dispatcher\Traits\RouterTrait;
use \Comodojo\Dispatcher\Traits\RouteTrait;
use \Comodojo\Dispatcher\Traits\ExtraTrait;
use \Comodojo\Foundation\Events\EventsTrait;
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
 * @license     MIT
 *
 * LICENSE:
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

class Dispatcher extends AbstractModel {

    use TimingTrait;
    use CacheTrait;
    use ServerCacheTrait;
    use RequestTrait;
    use ResponseTrait;
    use RouterTrait;
    use RouteTrait;
    use ExtraTrait;
    use EventsTrait;

    /**
     * The main dispatcher constructor.
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
        $configuration_object = new Configuration(DefaultConfiguration::get());
        $configuration_object->merge($configuration);

        $logger = is_null($logger) ? LogManager::createFromConfiguration($configuration_object)->getLogger() : $logger;

        parent::__construct($configuration_object, $logger);

        $this->logger->debug("--------------------------------------------------------");
        $this->logger->debug("Dispatcher run-cycle starts at ".$this->getTime()->format('c'));

        try {

            // init other components
            $this->setEvents(is_null($events) ? EventsManager::create($this->logger) : $events);
            $this->setCache(is_null($cache) ? SimpleCacheManager::createFromConfiguration($this->configuration, $this->logger) : $cache);
            $this->setServerCache(new ServerCache($this->getCache()));

            // init models
            $this->setExtra(new Extra($this->logger));
            $this->setRequest(new Request($this->configuration, $this->logger));
            $this->setRouter(new Router($this->configuration, $this->logger, $this->cache, $this->events, $this->extra));
            $this->setResponse(new Response($this->configuration, $this->logger));

        } catch (Exception $e) {

            $this->logger->critical($e->getMessage(), $e->getTrace());

            throw $e;

        }

        // we're ready!
        $this->logger->debug("Dispatcher ready");

    }

    public function dispatch() {

        $configuration = $this->getConfiguration();
        $logger = $this->getLogger();
        $events = $this->getEvents();
        $request = $this->getRequest();
        $response = $this->getResponse();

        $logger->debug("Starting to dispatch.");
        $logger->debug("Emitting global dispatcher event.");
        $events->emit(new DispatcherEvent($this));

        // if dispatcher is administratively disabled, halt the process immediately
        if ($configuration->get('enabled') === false) {

            $logger->debug("Dispatcher disabled, shutting down gracefully.");

            $status = $configuration->get('disabled-status');
            $content = $configuration->get('disabled-message');

            $response
                ->getStatus()
                ->set($status === null ? 503 : $status);
            $response
                ->getContent()
                ->set($content === null ? 'Service unavailable (dispatcher disabled)' : $content);

            return $this->shutdown();

        }

        $cache = $this->getServerCache();

        $events->emit($this->createServiceSpecializedEvents('dispatcher.request'));
        $events->emit($this->createServiceSpecializedEvents('dispatcher.request.'.$this->request->getMethod()->get()));
        $events->emit($this->createServiceSpecializedEvents('dispatcher.request.#'));

        $logger->debug("Starting router");

        try {

            $route = $this->getRouter()
                ->route($this->request);
            $this->setRoute($route);

        } catch (DispatcherException $de) {

            $logger->debug("Route error (".$de->getStatus()."), shutting down dispatcher");
            $this->processDispatcherException($de);
            return $this->shutdown();

        }

        $route_type = $route->getType();
        $route_service = $route->getServiceName();

        $logger->debug("Route acquired, type $route_type");

        $events->emit($this->createServiceSpecializedEvents('dispatcher.route'));
        $events->emit($this->createServiceSpecializedEvents('dispatcher.route.'.$route_type));

        if ( $route_type === 'ROUTE' ) {
            $logger->debug("Route leads to service $route_service");
            $events->emit($this->createServiceSpecializedEvents('dispatcher.route.'.$route_service));
        }

        $events->emit($this->createServiceSpecializedEvents('dispatcher.route.#'));

        if ($cache->read($this->request, $this->response)) {
            // we have a cache!
            // shutdown immediately
            return $this->shutdown();
        }

        try {

            switch ($route_type) {

                case 'ROUTE':

                    $logger->debug("Running $route_service service");

                    // translate route to service
                    $this->router->compose($this->response);

                    $this->processConfigurationParameters($this->route);

                    $cache->dump($this->request, $this->response, $this->route);

                    break;

                case 'REDIRECT':

                    $logger->debug("Redirecting response...");

                    Redirect::compose(
                        $this->getRequest(),
                        $this->getResponse(),
                        $this->getRoute()
                    );

                    break;

                case 'ERROR':

                    $logger->debug("Sending error message...");

                    Error::compose($this->route);

                    break;
            }

        } catch (DispatcherException $de) {

            $logger->debug("Service exception (".$de->getStatus()."), shutting down dispatcher");
            $this->processDispatcherException($de);

        }

        return $this->shutdown();

    }

    private function processConfigurationParameters($route) {

        $params = $route->getParameter('headers');

        if ( !empty($params) && is_array($params) ) {
            foreach($params as $name => $value) {
                $this->getResponse()->getHeaders()->set($name, $value);
            }
        }

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

    /**
     * @param string $name
     */
    private function createServiceSpecializedEvents($name) {

        $this->logger->debug("Emitting $name service-event");

        return new ServiceEvent(
            $name,
            $this->getConfiguration(),
            $this->getLogger(),
            $this->getCache(),
            $this->getServerCache(),
            $this->getEvents(),
            $this->getRequest(),
            $this->getRouter(),
            $this->getResponse(),
            $this->getExtra()
        );

    }

    private function shutdown() {

        $configuration = $this->getConfiguration();
        $logger = $this->getLogger();
        $events = $this->getEvents();
        $request = $this->getRequest();
        $response = $this->getResponse();

        $events->emit($this->createServiceSpecializedEvents('dispatcher.response'));
        $events->emit($this->createServiceSpecializedEvents('dispatcher.response.'.$response->getStatus()->get()));
        $events->emit($this->createServiceSpecializedEvents('dispatcher.response.#'));

        $response->consolidate($request, $this->route);

        $logger->debug("Composing return value");

        $return = Processor::parse(
            $configuration,
            $logger,
            $request,
            $response
        );

        $logger->debug("Dispatcher run-cycle ends");

        // This could cause WSOD with some PHP-FPM configurations
        // if ( function_exists('fastcgi_finish_request') ) fastcgi_finish_request();
        // else ob_end_clean();
        ob_end_clean();

        return $return;

    }

}
