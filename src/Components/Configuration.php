<?php namespace Comodojo\Dispatcher\Components;

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

 // define('DISPATCHER_LOG_ENABLED', false);
 //
 // define('DISPATCHER_LOG_NAME', 'dispatcher');
 //
 // define('DISPATCHER_LOG_TARGET', null);
 //
 // define('DISPATCHER_LOG_LEVEL', 'ERROR');
 //
 // define("DISPATCHER_REAL_PATH", realpath(dirname(__FILE__)."/../")."/");
 //
 // define("DISPATCHER_BASEURL","");
 //
 // define ('DISPATCHER_ENABLED', true);
 //
 // define ('DISPATCHER_USE_REWRITE', true);
 //
 // define('DISPATCHER_AUTO_ROUTE', false);
 //
 // define('DISPATCHER_CACHE_ENABLED', true);
 //
 // define('DISPATCHER_CACHE_DEFAULT_TTL', 600);
 //
 // define('DISPATCHER_CACHE_FAIL_SILENTLY', true);
 //
 // define('DISPATCHER_DEFAULT_ENCODING', 'UTF-8');
 //
 // define('DISPATCHER_SUPPORTED_METHODS', 'GET,PUT,POST,DELETE,OPTIONS,HEAD');
 //
 // define('DISPATCHER_CACHE_FOLDER', DISPATCHER_REAL_PATH."cache/");
 //
 // define('DISPATCHER_SERVICES_FOLDER', DISPATCHER_REAL_PATH."services/");
 //
 // define('DISPATCHER_PLUGINS_FOLDER', DISPATCHER_REAL_PATH."plugins/");
 //
 // define('DISPATCHER_TEMPLATES_FOLDER', DISPATCHER_REAL_PATH."templates/");
 //
 // define('DISPATCHER_LOG_FOLDER', DISPATCHER_REAL_PATH."logs/");

class Configuration {

    private $attributes = array(
        'DISPATCHER_ENABLED' => true,
        'DISPATCHER_SUPPORTED_METHODS' => 'GET,PUT,POST,DELETE,OPTIONS,HEAD',
        'DISPATCHER_DEFAULT_ENCODING' => 'UTF-8',
        'DISPATCHER_AUTO_ROUTE' => false
    );

    public function __construct() {

        $this->attributes['DISPATCHER_BASE_URL'] = self::urlGetAbsolute();

        $constants = get_defined_constants(true);

        if ( !isset($constants['user']) ) return;

        $dispatcher_constants = preg_grep("/^DISPATCHER_/", array_keys($constants['user']));

        if ( sizeof($dispatcher_constants) > 0 ) {

            $filtered_constants = array_intersect_key($constants['user'], array_flip($dispatcher_constants));

            $this->attributes = array_merge($this->attributes, $filtered_constants);

        }

    }

    final public function __get($property) {

        if (array_key_exists($property, $this->attributes)) {

            return $this->attributes[$property];

        }

        return null;

    }

    final public function __isset($property) {

        return isset($this->attributes[$property]);

    }

    private static function urlGetAbsolute() {

        $http = 'http' . ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 's' : '') . '://';

        $uri = preg_replace("/\/index.php(.*?)$/i", "", $_SERVER['PHP_SELF']);

        return ( $http . $_SERVER['HTTP_HOST'] . $uri . "/" );

    }

}
