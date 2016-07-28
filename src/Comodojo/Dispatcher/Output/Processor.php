<?php namespace Comodojo\Dispatcher\Output;

use \Comodojo\Dispatcher\Output\HttpStatus\StatusGeneric;
use \Comodojo\Dispatcher\Components\Model as DispatcherClassModel;
use \Comodojo\Dispatcher\Request\Model as Request;
use \Comodojo\Dispatcher\Response\Model as Response;
use \Comodojo\Dispatcher\Components\Configuration;
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

    private $codes = array(
        // Informational 1xx
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        // Successful 2xx
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status', // missing
        208 => 'Already Reported', // missing
        226 => 'IM Used', // missing
        // Redirection 3xx
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',
        // Client Error 4xx
        400 => 'Bad Request',
        401 => 'Unauthorized', // missing
        402 => 'Payment Required', // missing
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable', // missing
        407 => 'Proxy Authentication Required', // missing
        408 => 'Request Timeout', // missing
        409 => 'Conflict', // missing
        410 => 'Gone',
        411 => 'Length Required', // missing
        412 => 'Precondition Failed', // missing
        413 => 'Payload Too Large', // missing
        414 => 'URI Too Long', // missing
        415 => 'Unsupported Media Type', // missing
        416 => 'Range Not Satisfiable', // missing
        417 => 'Expectation Failed', // missing
        421 => 'Misdirected Request', // missing
        422 => 'Unprocessable Entity', // missing
        423 => 'Locked', // missing
        424 => 'Failed Dependency', // missing
        426 => 'Upgrade Required', // missing
        428 => 'Precondition Required', // missing
        429 => 'Too Many Requests', // missing
        431 => 'Request Header Fields Too Large', // missing
        451 => 'Unavailable For Legal Reasons', // missing
        // Server Error 5xx
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates (Experimental)', // missing
        507 => 'Insufficient Storage', // missing
        508 => 'Loop Detected', // missing
        510 => 'Not Extended', // missing
        511 => 'Network Authentication Required' // missing
    );

    private $request;

    private $response;

    public function __construct(Configuration $configuration, LoggerInterface $logger, Request $request, Response $response) {

        parent::__construct($configuration, $logger);

        $this->response = $response;

        $this->request = $request;

    }

    public function send() {

        $status = $this->response->status()->get();

        if ( !array_key_exists($status, $this->codes) ) throw new Exception("Invalid HTTP status code in response");

        $message = $this->codes[$status];

        $this->response->headers()->send();

        header(sprintf('HTTP/%s %s %s', $this->request->version()->get(), $status, $message), true, $status);

        $this->response->cookies()->save();

        return $this->response->content()->get();

    }

    public static function parse(Configuration $configuration, LoggerInterface $logger, Request $request, Response $response) {

        $processor = new Processor($configuration, $logger, $request, $response);

        return $processor->send();

    }

}
