<?php namespace Comodojo\Dispatcher\Components;

use \Symfony\Component\Yaml\Yaml;

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


class Configuration {

    protected $attributes = array();

    public function __construct( $configuration = array() ) {

        $this->attributes = array_merge($this->attributes, $configuration);

    }

    final public function get($property = null) {

        if ( is_null($property) ) {

            return $this->attributes;

        } else if (array_key_exists($property, $this->attributes)) {

            $value = $this->attributes[$property];

            // substitution by backreference is cool but hard compute for a large set of values :(
            //
            // if ( is_scalar($value) && preg_match_all('/%(.+?)%/', $value, $matches, PREG_SET_ORDER) ) {
            //
            //     $substitutions = array();
            //
            //     foreach ( $matches as $match ) {
            //
            //         $backreference = $match[1];
            //
            //         if ( $backreference != $property && !isset($substitutions['/%'.$backreference.'%/']) ) {
            //
            //             $substitutions['/%'.$backreference.'%/'] = $this->get($backreference);
            //
            //         }
            //
            //     }
            //
            //     $value = preg_replace(array_keys($substitutions), array_values($substitutions), $value);
            //
            // }

            return $value;

        } else {

            return null;

        }

    }

    final public function set($property, $value) {

        $this->attributes[$property] = $value;

        return $this;

    }

    final public function isDefined($property) {

        return isset($this->attributes[$property]);

    }

    final public function delete($property = null) {

        if ( is_null($property) ) {

            $this->attributes = array();

            return true;

        } else if ( $this->isDefined($property) ) {

            unset($this->attributes[$property]);

            return true;

        } else {

            return false;

        }

    }

    final public function merge($properties) {

        $this->attributes = array_replace($this->attributes, $properties);

        return $this;

    }

    public function loadFromYaml($yaml) {

        $configuration = Yaml::parse($yaml);

        return $this->merge($configuration);

    }

}
