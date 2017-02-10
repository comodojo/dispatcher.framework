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
    
    public function __construct(Configuration $configuration, LoggerInterface $logger) {

        parent::__construct($configuration, $logger);

        $this->setHeaders(new Headers());
        $this->setCookies(new CookieManager());
        $this->setStatus(new Status());
        $this->setContent(new Content());
        $this->setLocation(new Location());

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

    public function consolidate(Request $request, Route $route = null) {

        $status = $this->getStatus()->get();

        $output_class_name = "\\Comodojo\\Dispatcher\\Response\\Preprocessor\\Status".$status;

        // @TODO: this condition will be removed when all preprocessors ready
        if (class_exists($output_class_name)) {
            $output = new $output_class_name($this);
        } else {
            $output = new \Comodojo\Dispatcher\Response\Preprocessor\Status200($this);
        }

        $output->consolidate();

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
            $timestamp = (int)$this->getTime()->format('U')+$ttl;

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
