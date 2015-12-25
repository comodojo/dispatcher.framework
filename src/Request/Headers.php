<?php namespace Comodojo\Dispatcher\Request;

use \Comodojo\Dispatcher\Components\Headers as HeadersTrait;

/**
 *
 * @package     Comodojo dispatcher
 * @author      Marco Giovinazzi <marco.giovinazzi@comodojo.org>
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

class Headers {

    use HeadersTrait;

    public function __construct() {

        $this->headers = self::getHeaders();

    }

    /**
     * Get request headers both in apache and nginx
     *
     * @return array
     */
    private static function getHeaders() {

        if ( function_exists('getallheaders') ) return getallheaders();

        $headers = array();

        foreach ( $_SERVER as $name => $value ) {

            if ( substr($name, 0, 5) == 'HTTP_' ) {

                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;

            }

        }

        return $headers;

    }

}
