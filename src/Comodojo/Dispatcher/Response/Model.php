<?php namespace Comodojo\Dispatcher\Response;

use \Comodojo\Dispatcher\Components\AbstractModel;
use \Comodojo\Dispatcher\Request\Model as Request;
use \Comodojo\Dispatcher\Router\Route;
use \Comodojo\Foundation\Timing\TimingTrait;
use \Comodojo\Foundation\Base\Configuration;
use \Comodojo\Cookies\CookieManager;
use \Psr\Log\LoggerInterface;
use \Serializable;

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

class Model extends AbstractModel implements Serializable {

    use TimingTrait;

    protected static $no_content_statuses = [100, 101, 102, 204, 304];

    protected static $cacheable_methods = ['GET', 'HEAD', 'POST', 'PUT'];

    protected static $cacheable_statuses = [200, 203, 300, 301, 302, 404, 410];

    protected $headers;

    protected $cookies;

    protected $status;

    protected $content;

    protected $location;

    protected $preprocessor;

    public function __construct(Configuration $configuration, LoggerInterface $logger) {

        parent::__construct($configuration, $logger);

        $this->setHeaders(new Headers())
            ->setCookies(new CookieManager())
            ->setStatus(new Status())
            ->setContent(new Content())
            ->setLocation(new Location())
            ->setPreprocessor(new Preprocessor())
            ->setTiming();

    }

    public function getHeaders() {

        return $this->headers;

    }

    public function setHeaders(Headers $headers) {

        $this->headers = $headers;

        return $this;

    }

    public function getCookies() {

        return $this->cookies;

    }

    public function setCookies(CookieManager $cookies) {

        $this->cookies = $cookies;

        return $this;

    }

    public function getStatus() {

        return $this->status;

    }

    public function setStatus(Status $status) {

        $this->status = $status;

        return $this;

    }

    public function getContent() {

        return $this->content;

    }

    public function setContent(Content $content) {

        $this->content = $content;

        return $this;

    }

    public function getLocation() {

        return $this->location;

    }

    public function setLocation(Location $location) {

        $this->location = $location;

        return $this;

    }

    /**
     * Get the current preprocessor
     *
     * @return Preprocessor
     */
    public function getPreprocessor() {

        return $this->preprocessor;

    }

    /**
     * Set the current preprocessor
     *
     * @param Preprocessor $preprocessor
     *  The preprocessor to use
     * @return Model
     */
    public function setPreprocessor(Preprocessor $preprocessor) {

        $this->preprocessor = $preprocessor;

        return $this;

    }

    public function serialize() {

        return serialize($this->export());

    }

    public function unserialize($data) {

        $this->import(unserialize($data));

    }

    public function export() {

        return (object)[
            'headers' => $this->getHeaders(),
            'cookies' => $this->getCookies()->getAll(),
            'status' => $this->getStatus(),
            'content' => $this->getContent(),
            'location' => $this->getLocation()
        ];

    }

    public function import($data) {

        if (isset($data->headers)) $this->setHeaders($data->headers);
        if (isset($data->status)) $this->setStatus($data->status);
        if (isset($data->content)) $this->setContent($data->content);
        if (isset($data->location)) $this->setLocation($data->location);

        if (isset($data->cookies) && is_array($data->cookies)) {
            $cookies = $this->getCookies();
            foreach ($data->cookies as $name => $cookie) $cookies->add($cookie);
        }

    }

    public function consolidate(Request $request, Route $route=null) {

        $status = $this->getStatus()->get();
        $preprocessor = $this->getPreprocessor()->get($status);
        $preprocessor->consolidate($this);

        if ($route != null) {
            $this->setClientCache($request, $route);
        }

        // extra checks
        $content = $this->getContent();
        $headers = $this->getHeaders();

        if ((string)$request->getMethod() == 'HEAD' && !in_array($status, self::$no_content_statuses)) {
            $length = $content->length();
            $content->set(null);
            if ($length) {
                $headers->set('Content-Length', $length);
            }
        }

        if ($headers->get('Transfer-Encoding') != null) {
            $headers->delete('Content-Length');
        }

        if ((string)$request->getVersion() == '1.0' && false !== strpos($headers->get('Cache-Control'), 'no-cache')) {
            $headers->set('pragma', 'no-cache');
            $headers->set('expires', -1);
        }

    }

    private function setClientCache(Request $request, Route $route) {

        $cache = strtoupper($route->getParameter('cache'));
        $ttl = (int)$route->getParameter('ttl');

        if (
            ($cache == 'CLIENT' || $cache == 'BOTH') &&
            in_array((string)$request->getMethod(), self::$cacheable_methods) &&
            in_array($this->getStatus()->get(), self::$cacheable_statuses)
            // @TODO: here we should also check for Cache-Control no-store or private;
            //        the cache layer will be improoved in future versions.
        ) {

            $headers = $this->getHeaders();
            $timestamp = (int) $this->getTime()->format('U')+$ttl;

            if ($ttl > 0) {

                $headers->set("Cache-Control", "max-age=".$ttl.", must-revalidate");
                $headers->set("Expires", gmdate("D, d M Y H:i:s", $timestamp)." GMT");

            } else {

                $headers->set("Cache-Control", "no-cache, must-revalidate");
                $headers->set("Expires", "Mon, 26 Jul 1997 05:00:00 GMT");

            }

        }

    }

}
