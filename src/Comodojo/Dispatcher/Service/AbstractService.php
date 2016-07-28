<?php namespace Comodojo\Dispatcher\Service;

use \Comodojo\Dispatcher\Components\Model as DispatcherClassModel;
use \Comodojo\Dispatcher\Components\Configuration;
use \Comodojo\Dispatcher\Request\Model as Request;
use \Comodojo\Dispatcher\Router\Model as Router;
use \Comodojo\Dispatcher\Response\Model as Response;
use \Comodojo\Dispatcher\Extra\Model as Extra;
use \Psr\Log\LoggerInterface;
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

abstract class AbstractService extends DispatcherClassModel {

    public function __construct(
        Configuration $configuration,
        LoggerInterface $logger,
        Request $request,
        Router $router,
        Response $response,
        Extra $extra
    ) {

        parent::__construct($configuration, $logger);

        $this->request = $request;

        $this->router = $router;

        $this->response = $response;

        $this->extra = $extra;

    }

    /**
     * Get service-implemented HTTP methods
     *
     * @return  array   Service implemented methods, in uppercase
     * @throw Exception
     */
    public function getImplementedMethods() {

        $supported_methods = $this->configuration->get('supported-http-methods');

        if ( is_null($supported_methods) ) $supported_methods = array('GET','PUT','POST','DELETE','OPTIONS','HEAD','TRACE','CONNECT','PURGE');

        if ( method_exists($this, 'any') ) {

            return $supported_methods;

        }

        $implemented_methods = array();

        foreach ( $supported_methods as $method ) {

            if ( method_exists($this, strtolower($method)) ) array_push($implemented_methods,$method);

        }

        return $implemented_methods;

    }

    /**
     * Return the callable class method that reflect the requested one
     *
     */
    public function getMethod($method) {

        if ( method_exists($this, strtolower($method)) ) {

            return strtolower($method);

        } else if ( method_exists($this, strtolower('any')) ) {

            return 'any';

        } else {

            return "any";

        }

    }

}
