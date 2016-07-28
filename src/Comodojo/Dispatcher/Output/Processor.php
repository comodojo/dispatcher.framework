<?php namespace Comodojo\Dispatcher\Output;

use \Comodojo\Dispatcher\Components\Model as DispatcherClassModel;
use \Comodojo\Dispatcher\Request\Model as Request;
use \Comodojo\Dispatcher\Response\Model as Response;
use \Comodojo\Dispatcher\Components\Configuration;
use \Comodojo\Dispatcher\Components\HttpStatusCodes;
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

//
// @WARNING: some method's preprocessor missing
//
//         _______
//        j_______j
//       /_______/_\
//       |Missing| |
//       |  ___  | |
//       | !418! | |
//       | !___! | |
//       |_______|,'
//

class Processor extends DispatcherClassModel {

    public function __construct(Configuration $configuration, LoggerInterface $logger, Request $request, Response $response) {

        parent::__construct($configuration, $logger);

        $this->response = $response;

        $this->request = $request;
        
        $this->codes = new HttpStatusCodes();

    }

    public function send() {

        $status = $this->response->status->get();

        if ( !$this->codes->exists($status) ) throw new Exception("Invalid HTTP status code in response");

        $message = $this->codes->getMessage($status);

        $this->response->headers->send();

        header(sprintf('HTTP/%s %s %s', $this->request->version->get(), $status, $message), true, $status);

        $this->response->cookies->save();

        return $this->response->content->get();

    }

    public static function parse(Configuration $configuration, LoggerInterface $logger, Request $request, Response $response) {

        $processor = new Processor($configuration, $logger, $request, $response);

        return $processor->send();

    }

}
