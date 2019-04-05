<?php namespace Comodojo\Dispatcher\Cache;

use \Comodojo\SimpleCache\Manager as CacheManager;
use \Comodojo\Dispatcher\Traits\CacheTrait;

/**
 * Abstract internal cache handler
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

abstract class AbstractCache {

    use CacheTrait;

    /**
     * @const int DEFAULTTTL
     *  Default cache TTL, in seconds (24h)
     */
    const DEFAULTTTL = 86400;

    public function __construct(CacheManager $cache) {

        $this->setCache($cache);

    }

}
