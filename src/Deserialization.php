<?php namespace Comodojo\Dispatcher;

use \Exception;

/**
 * deserialization class for dispatcher
 * 
 * @package     Comodojo dispatcher
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

class Deserialization {

    /**
     * Convert JSON to Array using PHP json_decode func
     *
     * Setting $raw to true, JSON objects will be converted into PHP
     * objects; if false, to array.
     *
     * @param   string  $data   Data to convert
     * @param   bool    $raw    Raw conversion
     *
     * @return  array
     */
    final public function fromJson($data, $raw=false) {

        if ( !is_string($data) ) throw new Exception("Invalid data for JSON deserialization");

        return json_decode($data, !$raw);

    }

    /**
     * Convert XML to Array using comodojo XML converter
     *
     * @param   string  $data   Data to convert
     *
     * @return  array
     */
    final public function fromXml($data) {

        if ( !is_string($data) ) throw new Exception("Invalid data for XML deserialization");

        $xmlEngine = new XML();
        $xmlEngine->sourceString = $data;

        return $xmlEngine->decode();

    }

    /**
     * Convert YAML to Array using Spyc converter
     *
     * @param   string  $data   Data to convert
     *
     * @return  array
     */
    final public function fromYaml($data) {

        if ( !is_string($data) ) throw new Exception("Invalid data for YAML deserialization");

        return \Spyc::YAMLLoadString($data);

    }

    /**
     * Convert serialized export Array using PHP unserialize
     *
     * @param   string  $data   Data to convert
     *
     * @return  array
     */
    final public function fromExport($data) {

        if ( !is_string($data) ) throw new Exception("Invalid data for EXPORT deserialization");

        return unserialize($data);

    }

}