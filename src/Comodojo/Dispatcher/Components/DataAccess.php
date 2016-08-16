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


trait DataAccess {

    protected $data = array();

    public function __get($name) {

        if ( array_key_exists($name, $this->data) ) {

            return $this->data[$name];

        }

        return null;

    }

    public function __set($name, $value) {

        $this->data[$name] = $value;

    }

    public function __isset($name) {

        return array_key_exists($name, $this->data);

    }

    public function merge($data) {

        foreach ($data as $key => $value) {
            $this->$key = $value;
        }

        return $this;

    }

    public function export() {

        return $this->data;

    }

    public function import($data) {

        $this->data = $data;

        return $this;

    }

}
