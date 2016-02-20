<?php namespace Comodojo\Dispatcher\Components;

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


trait Headers {

    protected $headers = array();

    public function get($header = null) {

        if ( is_null($header) ) return $this->headers;

        else if ( array_key_exists($header, $this->headers) ) return $this->headers[$header];

        else return null;

    }

    public function getAsString($header = null) {

        if ( is_null($header) ) {

            return array_map( function($header, $value) {
                return (string)($header.':'.$value);
            },
            $this->headers);

        } else if ( array_key_exists($header, $this->headers) ) {

            return (string)($header.':'.$this->headers[$header]);

        } else return null;

    }

    public function set($header, $value=null) {

        if ( is_null($value) ) {

            $header = explode(":", $header);

            $this->headers[$header[0]] = isset($header[1]) ? $header[1] : '';

        } else {

            $this->headers[$header] = $value;

        }

        return $this;

    }

    public function delete($header = null) {

        if ( is_null($header) ) {

            $this->headers = array();

            return true;

        } else if ( array_key_exists($header, $this->headers) ) {

            unset($this->headers[$header]);

            return true;

        } else {

            return false;

        }

    }

}
