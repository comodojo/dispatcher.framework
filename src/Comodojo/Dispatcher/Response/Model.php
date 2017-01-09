<?php namespace Comodojo\Dispatcher\Response;

use \Comodojo\Dispatcher\Components\AbstractModel;
use \Comodojo\Dispatcher\Response\Headers;
use \Comodojo\Dispatcher\Response\Status;
use \Comodojo\Dispatcher\Response\Content;
use \Comodojo\Dispatcher\Response\Location;
use \Comodojo\Dispatcher\Request\Model as Request;
use \Comodojo\Dispatcher\Router\Route;
use \Comodojo\Foundation\Timing\TimingTrait;
use \Comodojo\Foundation\Base\Configuration;
use \Comodojo\Cookies\CookieManager;
use \Psr\Log\LoggerInterface;

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

class Model extends AbstractModel {

    use TimingTrait;

    protected $mode = self::PROTECTDATA;

    public function __construct(Configuration $configuration, LoggerInterface $logger) {

        parent::__construct($configuration, $logger);

        $this->setRaw('headers', new Headers());
        $this->setRaw('cookies', new CookieManager());
        $this->setRaw('status', new Status());
        $this->setRaw('content', new Content());
        $this->setRaw('location', new Location());

    }

    public function consolidate(Request $request, Route $route = null) {

        $status = $this->status->get();

        $output_class_name = "\\Comodojo\\Dispatcher\\Response\\Preprocessor\\Status".$status;

        // @TODO: this condition will be removed when all preprocessors ready
        if ( class_exists($output_class_name) ) {
            $output = new $output_class_name($this);
        } else {
            $output = new \Comodojo\Dispatcher\Response\Preprocessor\Status200($this);
        }

        $output->consolidate();

        if ( $route != null ) {
            $this->setClientCache($request, $route);
        }

        // extra checks

        if ( $request->method->get() == 'HEAD' && !in_array($status, array(100,101,102,204,304)) ) {
            $length = $this->content->length();
            $this->content->set(null);
            if ($length) $this->headers->set('Content-Length', $length);
        }

        if ($this->headers->get('Transfer-Encoding') != null) {
            $this->headers->delete('Content-Length');
        }

        if ( $request->version->get() == '1.0' && false !== strpos($this->headers->get('Cache-Control'), 'no-cache')) {
            $this->headers->set('pragma', 'no-cache');
            $this->headers->set('expires', -1);
        }

    }

    private function setClientCache(Request $request, Route $route) {

        $cache = strtoupper($route->getParameter('cache'));
        $ttl = $route->getParameter('ttl');

        if (
            ($cache == 'CLIENT' || $cache == 'BOTH') &&
            in_array($request->method->get(), array('GET', 'HEAD', 'POST', 'PUT')) &&
            in_array($this->status->get(), array(200, 203, 300, 301, 302, 404, 410))
            // @TODO: here we should also check for Cache-Control no-store or private;
            //        the cache layer will be improoved in future versions.
        ) {

            if ( $ttl > 0 ) {

                $this->headers->set("Cache-Control","max-age=".$ttl.", must-revalidate");
                $this->headers->set("Expires",gmdate("D, d M Y H:i:s", (int)$this->getTimestamp() + $ttl)." GMT");

            } else {

                $this->headers->set("Cache-Control","no-cache, must-revalidate");
                $this->headers->set("Expires","Mon, 26 Jul 1997 05:00:00 GMT");

            }

        }

    }

}
