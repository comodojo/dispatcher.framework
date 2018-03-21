<?php namespace Comodojo\Dispatcher\Cache;

use \Comodojo\Dispatcher\Request\Model as Request;
use \Comodojo\Dispatcher\Router\Route;
use \Comodojo\Dispatcher\Response\Model as Response;

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

class ServerCache extends AbstractCache {

    // @NOTE: Server cache will not consider cacheable POST or PUT requests
    //        because of dispatcher internal structure: if post request is cached
    //        subsequent requests will never reach the service.
    protected static $cachable_methods = ['GET', 'HEAD'];

    protected static $cachable_statuses = [200, 203, 300, 301, 302, 404, 410];

    protected static $cache_namespace = "DISPATCHERSERVICES";

    public function read(
        Request $request,
        Response $response
    ) {

        $name = self::getCacheName($request);

        $cache_object = $this->getCache()->setNamespace(self::$cache_namespace)->get($name);

        if (is_null($cache_object)) return false;

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
                ->set($name, $response->export(), $ttl === null ? self::DEFAULTTTL : intval($ttl));

        }

    }

    private static function getCacheName(Request $request) {

        return md5((string)$request->getMethod().(string)$request->getUri());

    }

}
