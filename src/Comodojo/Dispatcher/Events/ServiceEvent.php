<?php namespace Comodojo\Dispatcher\Events;

use \Comodojo\Foundation\Base\ConfigurationTrait;
use \Comodojo\Foundation\Logging\LoggerTrait;
use \Comodojo\Foundation\Events\EventsTrait;
use \Comodojo\Dispatcher\Traits\CacheTrait;
use \Comodojo\Dispatcher\Traits\RequestTrait;
use \Comodojo\Dispatcher\Traits\ResponseTrait;
use \Comodojo\Dispatcher\Traits\RouterTrait;
use \Comodojo\Dispatcher\Traits\ExtraTrait;
use \Comodojo\Dispatcher\Request\Model as Request;
use \Comodojo\Dispatcher\Router\Model as Router;
use \Comodojo\Dispatcher\Response\Model as Response;
use \Comodojo\Dispatcher\Extra\Model as Extra;
use \Comodojo\Foundation\Events\AbstractEvent;
use \Comodojo\SimpleCache\Manager as CacheManager;
use \Comodojo\Foundation\Events\Manager as EventsManager;
use \Comodojo\Foundation\Base\Configuration;
use \Psr\Log\LoggerInterface;

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

class ServiceEvent extends AbstractEvent {

    use ConfigurationTrait;
    use LoggerTrait;
    use CacheTrait;
    use EventsTrait;
    use RequestTrait;
    use RouterTrait;
    use ResponseTrait;
    use ExtraTrait;

    public function __construct(
        $name,
        Configuration $configuration,
        LoggerInterface $logger,
        CacheManager $cache,
        EventsManager $events,
        Request $request,
        Router $router,
        Response $response,
        Extra $extra
    ) {

        parent::__construct($name);

        $this->setConfiguration($configuration);
        $this->setLogger($logger);
        $this->setCache($cache);
        $this->setEvents($events);
        $this->setRequest($request);
        $this->setRouter($router);
        $this->setResponse($response);
        $this->setExtra($extra);

    }

}
