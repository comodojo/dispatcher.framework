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

trait Parameters {

    protected $parameters = array();

    final public function get($parameter=null) {

        if ( is_null($parameter) ) return $this->parameters;

        else if ( array_key_exists($parameter, $this->parameters) ) {

            return $this->parameters[$parameter];

        }

        else return null;

    }

    final public function set($parameter, $value) {

        $this->parameters[$parameter] = $value;

        return $this;

    }

    final public function remove($parameter = null) {

        if ( is_null($parameter) ) {

            $this->parameters = array();

            return true;

        } else if ( array_key_exists($parameter, $this->parameters) ) {

            unset($this->parameters[$parameter]);

            return true;

        } else {

            return false;

        }

    }

}
