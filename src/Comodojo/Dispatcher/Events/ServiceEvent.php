<?php namespace Comodojo\Dispatcher\Events;

use \Comodojo\Dispatcher\Request\Model as Request;
use \Comodojo\Dispatcher\Router\Model as Router;
use \Comodojo\Dispatcher\Response\Model as Response;
use \Comodojo\Dispatcher\Extra\Model as Extra;
use \Comodojo\Foundation\Events\AbstractEvent;
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

class ServiceEvent extends AbstractEvent {

    protected $logger;

    protected $request;

    protected $router;

    protected $response;

    protected $extra;

    public function __construct(
        $name,
        LoggerInterface $logger,
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

    public function getLogger() {

        return $this->logger;
    }

    public function getRequest() {

        return $this->request;
    }

    public function getRouter() {

        return $this->router;
    }

    public function getResponse() {

        return $this->response;
    }

    public function getExtra() {

        return $this->extra;
    }

}
