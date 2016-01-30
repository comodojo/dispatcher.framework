<?php namespace Comodojo\Dispatcher\Response;

use \Comodojo\Components\Model as DispatcherClassModel;
use \Comodojo\Dispatcher\Response\Headers;
use \Comodojo\Dispatcher\Response\Status;
use \Comodojo\Dispatcher\Response\Content;
use \Comodojo\Dispatcher\Response\Location;
use \Comodojo\Cookies\CookieManager;

/**
 *
 * @package     Comodojo dispatcher
 * @author      Marco Giovinazzi <marco.giovinazzi@comodojo.org>
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

class Model extends DispatcherClassModel {

    private $headers;

    private $cookies;

    private $status;

    private $content;

    private $location;

    private $content_type = "text/plain";

    private $charset;

    public function __construct(Configuration $configuration, Logger $logger) {

        parent::__construct($configuration, $logger);

        $this->headers = new Headers();

        $this->cookies = new CookieManager();

        $this->status = new Status();

        $this->content = new Content();

        $this->location = new Location();

    }

    public function headers() {

        return $this->headers;

    }

    public function cookies() {

        return $this->cookies;

    }

    public function status() {

        return $this->status;

    }

    public function content() {

        return $this->content;

    }

    public function location() {

        return $this->location;

    }

}
