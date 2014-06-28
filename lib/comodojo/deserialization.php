<?php namespace comodojo;

/**
 * deserialization class for dispatcher
 * 
 * @package 	Comodojo dispatcher (Spare Parts)
 * @author		comodojo <info@comodojo.org>
 * @license 	GPL-3.0+
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

class deserialization {

	public final function fromJSON($data, $raw=false) {

		if ( !is_string($data) ) throw new Exception("Invalid data for JSON deserialization");

		return json_decode($data, !$raw);

	}

	public final function fromXML($data) {

		if ( !is_string($data) ) throw new Exception("Invalid data for XML deserialization");

		//require "XML.php";

		$xmlEngine = new XML();
		$xmlEngine->sourceString = $data;

		return $xmlEngine->decode();

	}

	public final function fromYAML($data) {

		if ( !is_string($data) ) throw new Exception("Invalid data for YAML deserialization");

		//require DISPATCHER_REAL_PATH."/../spyc/Spyc.php";
		
		return \Spyc::YAMLLoadString($data);

	}

	public final function fromEXPORT($data) {

		if ( !is_string($data) ) throw new Exception("Invalid data for EXPORT deserialization");

		return unserialize($data);

	}

}

?>