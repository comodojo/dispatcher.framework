<?php namespace Comodojo\Dispatcher\Output;

use \Comodojo\Components\Model as DispatcherClassModel;
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

        $content = $this->response->content();

        $cookies = $this->response->cookies();

        $headers = $this->response->headers();

        $location = $this->response->location();

        // return value, just in case...
        $return = $content->get();

        switch ($status) {

            case 200: //OK

                header('Content-Length: '.$content->length());

                break;

            case 202: //Accepted

                //PLEASE NOTE: according to HTTP/1.1, 202 header SHOULD HAVE status description in body... just in case
                header($_SERVER["SERVER_PROTOCOL"].' 202 Accepted');
                header('Status: 202 Accepted');
                header('Content-Length: '.$content->length());

                break;

            case 204: //OK - No Content

                header($_SERVER["SERVER_PROTOCOL"].' 204 No Content');
                header('Status: 204 No Content');
                header('Content-Length: 0',true);

                $return = null;

                break;

            case 201: //Created
            case 301: //Moved Permanent
            case 302: //Found
            case 303: //See Other
            case 307: //Temporary Redirect

                header("Location: ".$location->get(),true,$status);

                break;

            case 304: //Not Modified

                $last_modified = $headers->get('Last-Modified');

                if ( is_null($last_modified) ) {

                    header($_SERVER["SERVER_PROTOCOL"].' 304 Not Modified');

                } else if ( is_int($last_modified) ) {

                    header('Last-Modified: '.gmdate('D, d M Y H:i:s', $last_modified).' GMT', true, 304);

                } else {

                    header('Last-Modified: '.$last_modified, true, 304);

                }

                header('Content-Length: '.$content->length());
                $headers->remove('Last-Modified');

                break;

            case 400: //Bad Request

                header($_SERVER["SERVER_PROTOCOL"].' 400 Bad Request', true, 400);
                header('Content-Length: '.$content->length());

                break;

            case 403:

                header('Origin not allowed', true, 403); //Not originated from allowed source

                break;

            case 404: //Not Found

                header($_SERVER["SERVER_PROTOCOL"].' 404 Not Found');
                header('Status: 404 Not Found');
                header('Content-Length: '.$content->length());

                break;

            case 405: //Not allowed

            header('Allow: ' . $value, true, 405);

            break;

            case 500: //Internal Server Error

                header('500 Internal Server Error', true, 500);
                header('Content-Length: '.$content->length());

                break;

            case 501: //Not implemented

            header('Allow: ' . $value, true, 501);

            break;

            case 503: //Service Unavailable

                header($_SERVER["SERVER_PROTOCOL"].' 503 Service Temporarily Unavailable');
                header('Status: 503 Service Temporarily Unavailable');

                // if ( $value !== false AND @is_int($value) ) {
                //     header('Retry-After: '.$value);
                // }

                break;

            default:

                header($_SERVER["SERVER_PROTOCOL"].' '.$this->response->status(), true, $status);
                header('Content-Length: '.$content->length());

                break;

        }

        $headers->send();

        $cookies->save();

        return $content;

    }

    public static function parse(Configuration $configuration, Logger $logger, Response $response) {

        $processor = new Processor($configuration, $logger, $response);

        return $processor->compose();

    }


}
