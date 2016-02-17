<?php namespace Comodojo\Dispatcher\Request;

use \Comodojo\Dispatcher\Components\Model as DispatcherClassModel;
use \Comodojo\Dispatcher\Request\Headers;
use \Comodojo\Dispatcher\Request\Post;
use \Comodojo\Dispatcher\Request\Query;
use \Comodojo\Dispatcher\Request\UserAgent;
use \Comodojo\Dispatcher\Request\Method;
use \Comodojo\Dispatcher\Components\Timestamp as TimestampTrait;
use \Comodojo\Dispatcher\Components\Configuration;
use \League\Uri\Schemes\Http as HttpUri;
use \Monolog\Logger;

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

class Model extends DispatcherClassModel {

    use TimestampTrait;

    private $headers;

    private $uri;

    private $useragent;

    private $post;

    private $query;

    private $method;

    public function __construct(Configuration $configuration, Logger $logger) {

        parent::__construct($configuration, $logger);

        $this->setTimestamp($_SERVER['REQUEST_TIME_FLOAT']);

        $this->headers = new Headers();

        $this->uri = HttpUri::createFromServer($_SERVER);

        $this->post = new Post();

        $this->query = new Query();

        $this->useragent = new UserAgent();

        $this->method = new Method();

    }

    public function headers() {

        return $this->headers;

    }

    public function uri() {

        return $this->uri;

    }

    public function post() {

        return $this->post;

    }

    public function query() {

        return $this->query;

    }

    public function useragent() {

        return $this->useragent;

    }

    public function method() {

        return $this->method;
    }

}
