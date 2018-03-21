<?php namespace Comodojo\Dispatcher\Router;

use \Comodojo\Dispatcher\Traits\CacheTrait;
use \Comodojo\Foundation\Events\EventsTrait;
use \Comodojo\Dispatcher\Traits\RequestTrait;
use \Comodojo\Dispatcher\Traits\ResponseTrait;
use \Comodojo\Dispatcher\Traits\ExtraTrait;
use \Comodojo\Dispatcher\Components\AbstractModel;
use \Comodojo\Dispatcher\Request\Model as Request;
use \Comodojo\Dispatcher\Response\Model as Response;
use \Comodojo\Dispatcher\Extra\Model as Extra;
use \Comodojo\Foundation\Base\Configuration;
use \Comodojo\Foundation\Events\Manager as EventsManager;
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

class Model extends AbstractModel {

    use CacheTrait;
    use EventsTrait;
    use RequestTrait;
    use ResponseTrait;
    use ExtraTrait;

    protected $bypass_routing = false;
    protected $bypass_service = false;
    protected $table;
    protected $route;

    public function __construct(
        Configuration $configuration,
        LoggerInterface $logger,
        SimpleCacheManager $cache,
        EventsManager $events,
        Extra $extra
    ) {

        parent::__construct($configuration, $logger);

        $this->setCache($cache);
        $this->setExtra($extra);
        $this->setEvents($events);

        $this->setTable(new Table($configuration, $logger, $cache));

    }

    public function getTable() {

        return $this->table;

    }

    public function setTable(Table $table) {

        $this->table = $table;

    }

    public function bypassRouting(Route $route) {

        $this->bypass_routing = true;

        $this->route = $route;

        return $this;

    }

    public function bypassService() {

        $this->bypass_service = true;

        return $this;

    }

    public function getRoute() {

        return $this->route;

    }

    public function route(Request $request) {

        $method = (string) $request->getMethod();

        $methods = $this->configuration->get('allowed-http-methods');

        if (!empty($methods) && in_array($method, $methods) === false) {

            throw new DispatcherException("Method not allowed", 0, null, 405, array(
                "Allow" => implode(",", $methods)
            ));

        }

        $this->setRequest($request);

        if ($this->bypass_routing === false) {

            if (!$this->parse()) throw new DispatcherException("Unable to find a valid route for the specified uri", 0, null, 404);

        }

        return $this->route;

    }

    public function getServiceInstance() {

        $class = $this->route->getClassName();

        if (class_exists($class)) {

            // All the route parameters are also added to the query parameters
            foreach ($this->route->getRequestParameters() as $parameter => $value) {
                $this->getRequest()->getQuery()->set($parameter, $value);
            }

            return new $class(
                $this->getConfiguration(),
                $this->getLogger(),
                $this->getCache(),
                $this->getEvents(),
                $this->getRequest(),
                $this,
                $this->getResponse(),
                $this->getExtra()
            );

        }

        return null;

    }

    public function compose(Response $response) {

        $this->setResponse($response);

        if (is_null($this->route)) {

            throw new DispatcherException("Route has not been loaded!");

        }

        if ($this->bypass_service) {

            return;

        }

        $service = $this->getServiceInstance();

        if (!is_null($service)) {

            $result = "";

            $method = (string)$this->getRequest()->getMethod();

            $methods = $service->getImplementedMethods();

            if (in_array($method, $methods)) {

                $callable = $service->getMethod($method);

                try {

                    $result = call_user_func([$service, $callable]);

                } catch (DispatcherException $de) {

                    throw $de;

                } catch (Exception $e) {

                    throw new DispatcherException(sprintf("Service '%s' execution failed for method '%s': %s", $this->route->getClassName(), $method, $e->getMessage()), 0, $e, 500);

                }

            } else {

                throw new DispatcherException(sprintf("Service '%s' doesn't implement method '%s'", $this->route->getServiceName(), $method), 0, null, 501, array(
                    "Allow" => implode(",", $methods)
                ));

            }

            $this->getResponse()->getContent()->set($result);

        } else {

            throw new DispatcherException(sprintf("Unable to execute service '%s'", $this->route->getClassName()), 0, null, 500);

        }

    }

    private function parse() {

        $path = urldecode($this->getRequest()->route());

        foreach ($this->table->getRoutes() as $regex => $value) {

            // The current uri is checked against all the global regular expressions associated with the routes
            if (preg_match("/".$regex."/", $path, $matches)) {

                /* If a route is matched, all the bits of the route string are evalued in order to create
                 * new query parameters which will be available for the service class
                 */
                $this->route = $value->path($matches);

                return true;

            }

        }

        return false;

    }

}
