<?php namespace Comodojo\Dispatcher\Events;

use \Comodojo\Dispatcher\Request\Model as Request;
use \Comodojo\Dispatcher\Router\Collector as Router;
use \Comodojo\Dispatcher\Response\Model as Response;
use \Comodojo\Dispatcher\Extra\Model as Extra;
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


class ServiceEvent extends AbstractEvent {

    private $logger;

    private $request;

    private $router;

    private $response;

    private $extra;

    public function __construct(
        $name,
        Logger $logger,
        Request $request,
        Router $router,
        Response $response,
        Extra $extra
    ) {

        parent::__construct($name);

        $this->logger = $logger;

        $this->request = $request;

        $this->router = $router;

        $this->response = $response;

        $this->extra = $extra;

    }

    final public function logger() {

        return $this->logger;

    }

    final public function request() {

        return $this->request;

    }

    final public function router() {

        return $this->router;

    }

    final public function response() {

        return $this->response;

    }

    final public function extra() {

        return $this->extra;

    }

}
