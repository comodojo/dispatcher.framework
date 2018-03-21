<?php namespace Comodojo\Dispatcher\Cache;

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

class RouterCache extends AbstractCache {

    const CACHE_NAMESPACE = "DISPATCHERINTERNALS";

    const CACHE_NAME = "dispatcher-routes";

    public function read() {

        return $this->getCache()->setNamespace(self::CACHE_NAMESPACE)->get(self::CACHE_NAME);

    }

    public function dump($data, $ttl = null) {

        return $this->getCache()
            ->setNamespace(self::CACHE_NAMESPACE)
            ->set(self::CACHE_NAME, $data, $ttl === null ? self::DEFAULTTTL : intval($ttl));

    }

}
