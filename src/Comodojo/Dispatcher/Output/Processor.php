<?php namespace Comodojo\Dispatcher\Output;

use \Comodojo\Dispatcher\Output\HttpStatus\StatusGeneric;
use \Comodojo\Dispatcher\Components\Model as DispatcherClassModel;
use \Comodojo\Dispatcher\Response\Model as Response;
use \Comodojo\Dispatcher\Components\Configuration;
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

// Missing status codes
//         _______
//        j_______j
//       /_______/_\
//       |Missing| |
//       |  ___  | |
//       | !100! | |
//       | !___! | |
//       |_______|,'
//
//      // Informational 1xx
//      100 => "Continue",
//      101 => "Switching Protocols",
//      // Successful 2xx
//      203 => "Non-Authoritative Information",
//      205 => "Reset Content",
//      206 => "Partial Content",
//      // Redirection 3xx
//      300 => "Multiple Choices",
//      305 => "Use Proxy",
//      // Client Error 4xx
//      401 => "Unauthorized",
//      402 => "Payment Required",
//      406 => "Not Acceptable",
//      407 => "Proxy Authentication Required",
//      408 => "Request Timeout",
//      409 => "Conflict",
//      410 => "Gone",
//      411 => "Length Required",
//      412 => "Precondition Failed",
//      413 => "Request Entity Too Large",
//      414 => "Request-URI Too Long",
//      415 => "Unsupported Media Type",
//      416 => "Requested Range Not Satisfiable",
//      417 => "Expectation Failed",
//      // Server Error 5xx
//      502 => "Bad Gateway",
//      504 => "Gateway Timeout",
//      505 => "HTTP Version Not Supported"

class Processor extends DispatcherClassModel {

    private $response;

    public function __construct(Configuration $configuration, Logger $logger, Response $response) {

        parent::__construct($configuration, $logger);

        $this->response = $response;

    }

    public function compose() {

        $status = $this->response->status()->get();

        $output_class_name = "\\Comodojo\\Dispatcher\\Output\\HttpStatus\\Status".$status;

        if ( class_exists($output_class_name) ) {

            $output = new $output_class_name($this->response);

        } else {

            $output = new StatusGeneric($this->response);

        }

        $output->consolidate();

        $this->response->headers()->send();

        $this->response->cookies()->save();

        return $this->response->content()->get();

    }

    public static function parse(Configuration $configuration, Logger $logger, Response $response) {

        $processor = new Processor($configuration, $logger, $response);

        return $processor->compose();

    }


}
