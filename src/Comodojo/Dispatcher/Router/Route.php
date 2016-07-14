<?php namespace Comodojo\Dispatcher\Router;

use \Comodojo\Dispatcher\Components\Model as DispatcherClassModel;
use \Comodojo\Dispatcher\Router\Model as Router;
use \Comodojo\Dispatcher\Request\Model as Request;
use \Comodojo\Dispatcher\Response\Model as Response;
use \Comodojo\Dispatcher\Extra\Model as Extra;
use \Comodojo\Dispatcher\Components\Timestamp as TimestampTrait;
use \Comodojo\Exception\DispatcherException;
use \Exception;

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

class Route extends DispatcherClassModel {

    use TimestampTrait;

    private $bypass = false;

    private $classname = "";

    private $type = "";

    private $service = "";

    private $router = null;

    public function __construct(
        Router $router
    ) {

        parent::__construct($router->configuration(), $router->logger());
        
        $this->router = $router;

        $this->setTimestamp();

    }

    public function getType() {

        return $this->type;

    }

    public function setType($type) {

        $this->type = $type;
        
        return $this;

    }

    public function getService() {

        return $this->service;

    }

    public function setService($service) {

        $this->service = $service;
        
        return $this;

    }

    public function getClassName() {

        return $this->classname;

    }

    public function setClassName($class) {

        $this->classname = $class;
        
        return $this;

    }

    public function getInstance(
        Request $request,
        Response $response,
        Extra $extra
    ) {

        $class = $this->classname;

        if (class_exists($class)) {

            return new $class(
                $this->configuration,
                $this->logger,
                $request,
                $this->router,
                $response,
                $extra
            );

        }
        else return null;

    }

}
