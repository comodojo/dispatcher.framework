<?php namespace Comodojo\Dispatcher\Cache;

/**
 * Router cache handler
 *
 * The dispatcher router uses the cache layer to store a "compiled" version of
 * the routing table, to speedup the parsing of configuration files at startup.
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

class RouterCache extends AbstractCache {

    /**
     * @const string CACHE_NAMESPACE
     *  Namespace for routing table cache
     */
    const CACHE_NAMESPACE = "DISPATCHERINTERNALS";

    /**
     * @const string CACHE_NAME
     *  Name for routing table cache
     */
    const CACHE_NAME = "dispatcher-routes";

    /**
     * Read the routing table from cache (if any)
     *
     * @return array
     */
    public function read() {

        return $this->getCache()
            ->setNamespace(self::CACHE_NAMESPACE)
            ->get(self::CACHE_NAME);

    }

    /**
     * Store the routing table in cache
     *
     * @return array $data
     * @return int $ttl
     * @return bool
     */
    public function dump($data, $ttl=null) {

        return $this->getCache()
            ->setNamespace(self::CACHE_NAMESPACE)
            ->set(self::CACHE_NAME, $data, $ttl === null ? self::DEFAULTTTL : intval($ttl));

    }

}
