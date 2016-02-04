<?php namespace Comodojo\Dispatcher\Routes;

use \Comodojo\Database\Database;
use \Comodojo\Base\Element;
use \Comodojo\Exception\DatabaseException;
use \Comodojo\Exception\ConfigurationException;
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

class RoutingTable implements RoutingTableInterface {

    private $routes = array();

    public function put($route, $type, $class, $parameters = array()) {

        $folders = explode("/", $route);

        $regex = $this->readpath($folders);

        if (!isset($this->routes[$regex])) {

            $this->add($folders, $type, $class, $parameters);

        }

    }

    public function set($route, $type, $class, $parameters = array()) {

        $folders = explode("/", $route);

        $regex = $this->readpath($folders);

        if (isset($this->routes[$regex])) {

            $this->add($folders, $type, $class, $parameters);

        }

    }

    public function get($route) {

        $folders = explode("/", $route);

        $regex = $this->readpath($folders);

        if (isset($this->routes[$regex]))
            return $this->routes[$regex];
        else
            return null;

    }

    public function remove($route) {

        $folders = explode("/", $route);

        $regex = $this->readpath($folders);

        if (isset($this->routes[$regex])) unset($this->routes[$regex]);

    }

    public function routes() {

        return $this->routes;

    }

    public function defaultRoute() {

        return $this->get('/');

    }

    private function readpath($folders = array(), &$value = null, $regex = '') {

        if (!empty($folders) && empty($folders[0])) array_shift($folders);

        if (empty($folders)) {

            return '^'.$regex.'[\/]?$';

        } else {

            $folder  = array_shift($folders);

            $decoded = json_decode($folder, true);

            if (!is_null($decoded) && is_array($decoded)) {

                $keys = array_keys($decoded);

                $param_regex    = '';

                $param_required = false;

                foreach ($decoded as $key => $string) {

                    $param_regex .= $this->readparam($key, $string, $param_required);

                }

                $this->readpath(
                    $folders,
                    $value,
                    $regex.'(?:\/'.$param_regex.')'. (($param_required)?'{1}':'?')
                );

            } else {

                array_push($value['service'], $folder);

                $this->readpath(
                    $folders,
                    $value,
                    $regex.'\/'.$folder
                );

            }

        }

    }

    private function readparam($key, $string, &$param_required) {

        $field_required = false;

        if (preg_match('/^(.+)\*$/', $key, $bits)) {

            $key            = $bits[1];
            $field_required = true;
            $param_required = true;

        }

        if (!is_null($value)) {

            $value['query'][$key] = array(
                'regex'    => $string,
                'required' => $required
            );

        }

        $string = preg_replace('/(?<!\\)\((?!\?)/', '(?:', $string);
        $string = preg_replace('/\.([\*\+])(?!\?)/', '.\${1}?', $string);

        return '(?P<' . $key . '>' . $string . ')' . (($field_required)?'{1}':'?');

    }

    private function add($folders, $type, $class, $parameters) {

        $folders = explode("/", $route);

        $value   = array(
            "type"       => $type,
            "class"      => $class,
            "service"    => array(),
            "parameters" => $parameters,
            "query"      => array()
        );

        $regex = $this->readpath($folders, $value);

        $this->routes[$regex] = $value;

    }

}
