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


class DefaultConfiguration {

    private static $configuration = array(
        'enabled' => true,
        'encoding' => 'UTF-8',
        'disabled-status' => 503,
        'disabled-message' => 'Dispatcher offline',
        'supported-methods' => array('GET','PUT','POST','DELETE','OPTIONS','HEAD')
    );

    public static function get() {

        $config = self::$configuration;

        $config['base-path'] = getcwd();

        $config['base-url'] = self::urlGetAbsolute();

        $config['base-uri'] = self::uriGetAbsolute();

        return $config;

    }

    private static function urlGetAbsolute() {

        $http = 'http' . ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 's' : '') . '://';

        $uri = self::uriGetAbsolute();

        return ( $http . $_SERVER['HTTP_HOST'] . $uri . "/" );

    }

    private static function uriGetAbsolute() {

        return preg_replace("/\/index.php(.*?)$/i", "", $_SERVER['PHP_SELF']);

    }

}