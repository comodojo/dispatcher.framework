<?php namespace Comodojo\Dispatcher;

/**
 * Header manipulation class for dispatcher
 *
 * This class manage response headers, from the status code to the custom
 * header the service will return.
 *
 * @package     Comodojo dispatcher (Spare Parts)
 * @author      Marco Giovinazzi <info@comodojo.org>
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

class Header {

    /**
     * Headers array
     *
     * @var     array
     */
    private $headers = Array();

    /**
     * Current time, as provided by dispatcher
     *
     * @var float
     */
    private $current_time = null;

    /**
     * Constructor method. It only acquire current time and notify that header engine is ready
     *
     * @param   string  $time   Dispatcher time
     */
    final public function __construct($time=false) {

        $this->current_time = $time !== false ? $time : time();

    }

    /**
     * Set $header with a $value. If value is null, only $header will be used
     *
     * @param   string  $time   Dispatcher time
     * @return  Object  $this
     */
    final public function set($header, $value=null) {

        $this->headers[$header] = $value;

        return $this;

    }

    /**
     * Get header value (if in array).
     *
     * @param   string  $time   Dispatcher time
     * @return  string|bool     Header value or false
     */
    final public function get($header) {

        if ( array_key_exists($header, $this->headers) ) return $this->headers[$header];
        else return false;

    }

    /**
     * Free recorded headers (re-init array)
     *
     * @return  Object  $this
     */
    final public function free() {

        $this->headers = Array();

        return $this;

    }

    /**
     * Compose return header (this is the part that handle status codes)
     *
     * This method uses a not-fixed $value that behave as:
     * - location, in case of redirect status code
     * - last modified time, in case of 304
     * - allowed method, in 405
     * - implemented method, in 501
     * - retry time, in 503 
     *
     * After status code setup, depending on status code, the method 
     * $this->processExtraHeaders() can be called to set all other headers
     *
     * @param   integer     $status         HTTP status code to return
     * @param   integer     $contentLength  Content length
     * @param   string      $value          (optional) value (see method description)
     */
    final public function compose($status, $contentLength=0, $value=false) {

        switch ($status) {

            case 200: //OK

                //if ($contentLength !== 0) header('Content-Length: '.$contentLength);

            header('Content-Length: '.$contentLength);

            $this->processExtraHeaders($this->headers);

            break;

            case 202: //Accepted

                //PLEASE NOTE: according to HTTP/1.1, 202 header SHOULD HAVE status description in body... just in case
            header($_SERVER["SERVER_PROTOCOL"].' 202 Accepted');
            header('Status: 202 Accepted');

                //if ($contentLength !== 0) header('Content-Length: '.$contentLength);

            header('Content-Length: '.$contentLength);

            $this->processExtraHeaders($this->headers);

            break;

            case 204: //OK - No Content

            header($_SERVER["SERVER_PROTOCOL"].' 204 No Content');
            header('Status: 204 No Content');
            header('Content-Length: 0',true);

            $this->processExtraHeaders($this->headers);
            
            break;

            case 201: //Created
            case 301: //Moved Permanent
            case 302: //Found
            case 303: //See Other
            case 307: //Temporary Redirect

            header("Location: ".$value,true,$status);
            
            break;

            case 304: //Not Modified

            if ( $value === false ) header($_SERVER["SERVER_PROTOCOL"].' 304 Not Modified');
            else header('Last-Modified: '.gmdate('D, d M Y H:i:s', $value).' GMT', true, 304);

                if ($contentLength !== 0) header('Content-Length: '.$contentLength); //is it needed?

                $this->processExtraHeaders($this->headers);

                break;

            case 400: //Bad Request

            header($_SERVER["SERVER_PROTOCOL"].' 400 Bad Request', true, 400);
                //if ($contentLength !== 0) header('Content-Length: '.$contentLength);

            header('Content-Length: '.$contentLength);
            
            break;

            case 403:

                header('Origin not allowed', true, 403); //Not originated from allowed source

                break;

            case 404: //Not Found

            header($_SERVER["SERVER_PROTOCOL"].' 404 Not Found');
            header('Status: 404 Not Found');
            header('Content-Length: '.$contentLength);

            break;

            case 405: //Not allowed

            header('Allow: ' . $value, true, 405); 

            break;

            case 500: //Internal Server Error

            header('500 Internal Server Error', true, 500);

            break;

            case 501: //Not implemented

            header('Allow: ' . $value, true, 501);
            
            break;

            case 503: //Service Unavailable

            header('HTTP/1.1 503 Service Temporarily Unavailable');
            header('Status: 503 Service Temporarily Unavailable');
            if ( $value !== false AND @is_int($value) ) {
                header('Retry-After: '.$value);
            }
            
            break;

        }

    }

    /**
     * Shortcut to set client cache headers
     *
     * @param   integer     $ttl    Cache time to live
     * @return  Object      $this
     */
    public function setClientCache($ttl) {

        $ttl = filter_var($ttl, FILTER_VALIDATE_INT);

        if ( $ttl > 0 ) {
            $this->set("Cache-Control","max-age=".$ttl.", must-revalidate");
            $this->set("Expires",gmdate("D, d M Y H:i:s", (int)$this->current_time + $ttl)." GMT");
        }
        else {
            $this->set("Cache-Control","no-cache, must-revalidate");
            $this->set("Expires","Mon, 26 Jul 1997 05:00:00 GMT");
        }

        return $this;

    }

    /**
     * Shortcut to set content type
     *
     * @param   string      $type       Content type
     * @param   string      $charset    Charset
     * @return  Object      $this
     */
    public function setContentType($type, $charset=null) {


        if ( is_null($charset) ) $this->set("Content-type",strtolower($type));
        else $this->set("Content-type",strtolower($type)."; charset=".$charset);

        return $this;

    }

    /**
     * Get request headers
     *
     * @return  Array   Headers sent with request
     */
    final public function getRequestHeaders() {

        $headers = '';

        if (function_exists('getallheaders')) $headers = getallheaders();

        else {

            foreach ($_SERVER as $name => $value) {

                if (substr($name, 0, 5) == 'HTTP_') $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }

        }

        return $headers;

    }

    /**
     * Process extra headers
     *
     * @param   array   $headers
     */
    private function processExtraHeaders($headers) {

        foreach ( $headers as $header => $value ) {

            if ( is_null($value) ) header($header, true);
            else  header($header.": ".$value, true);

        }

    }

}