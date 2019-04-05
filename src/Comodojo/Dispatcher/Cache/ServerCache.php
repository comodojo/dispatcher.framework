<?php namespace Comodojo\Dispatcher\Cache;

use \Comodojo\Dispatcher\Request\Model as Request;
use \Comodojo\Dispatcher\Router\Route;
use \Comodojo\Dispatcher\Response\Model as Response;

/**
 * Server-cache handler
 *
 * This class handles the process to read/write response content in the cache
 *
 * @NOTE: Server cache will not consider cacheable POST or PUT requests
 *  because of dispatcher internal structure: if post request is cached
 *  subsequent requests will never reach the service.
 *
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

class ServerCache extends AbstractCache {

    /**
     * @var array $cachable_methods
     *  Methods that allow cache usage
     */
    protected static $cachable_methods = ['GET', 'HEAD'];

    /**
     * @var array $cachable_statuses
     *  List of HTTP return codes that allow cache usage
     */
    protected static $cachable_statuses = [200, 203, 300, 301, 302, 404, 410];

    /**
     * @var string $cache_namespace
     *  Server-cache namespace
     */
    protected static $cache_namespace = "DISPATCHERSERVICES";

    /**
     * @var bool $bypass
     *  In case true, cache will be ignored (cache bypass)
     */
    protected $bypass = false;

    /**
     * Read the cached response starting from the request definition (if any)
     *
     * @param Request $request
     *  The request model
     * @param Response $response
     *  The response object that will be hydrated in case of success
     * @return bool
     */
    public function read(
        Request $request,
        Response $response
    ) {

        if ($this->bypass === true) {
            return false;
        }

        $name = self::getCacheName($request);
        $cache_object = $this->getCache()
            ->setNamespace(self::$cache_namespace)
            ->get($name);

        if (is_null($cache_object)) {
            return false;
        }

        $response->import($cache_object);
        return true;

    }

    /**
     * Dump the full request object in the cache
     *
     * @param Request $request
     *  The request model (to extract cache definition)
     * @param Response $response
     *  The response object that will be cached
     * @param Route $route
     *  The route model (to extract cache parameters)
     * @return bool
     */
    public function dump(
        Request $request,
        Response $response,
        Route $route
    ) {

        if ( $this->bypass === true ) {
            return false;
        }

        $cache = strtoupper($route->getParameter('cache'));
        $ttl = $route->getParameter('ttl');
        $name = self::getCacheName($request);
        $method = (string)$request->getMethod();
        $status = $response->getStatus()->get();

        if (
            ($cache == 'SERVER' || $cache == 'BOTH') &&
            in_array($method, self::$cachable_methods) &&
            in_array($status, self::$cachable_statuses)
        ) {
            $this->getCache()
                ->setNamespace(self::$cache_namespace)
                ->set(
                    $name,
                    $response->export(),
                    $ttl === null ? self::DEFAULTTTL : intval($ttl)
                );
            return true;
        }

        return false;

    }

    /**
     * Setup the cache bypass mode
     *
     * @return AbstractCache
     */
    public function bypassCache() {

        $this->bypass = true;
        return $this;

    }

    /**
     * Extract and compute the cache object name
     *
     * @param Request $request
     *  The request model
     * @return string
     */
    private static function getCacheName(Request $request) {

        return md5((string)$request->getMethod().(string)$request->getUri());

    }

}
