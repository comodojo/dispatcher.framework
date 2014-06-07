<?php namespace comodojo;

/**
 * standard spare parts serialize class
 * 
 * @package 	Comodojo Spare Parts
 * @author		comodojo.org
 * @copyright 	__COPYRIGHT__ comodojo.org (info@comodojo.org)
 * @version 	__CURRENT_VERSION__
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

class serialization {

	public final function toJSON($data, $flags) {

		if ( !( is_array($data) OR is_object($data) ) ) throw new Exception("Invalid data for JSON serialization");

		return json_encode($data, $flags);

	}

	public final function toXML($data, $prettfy=false) {

		if ( !( is_array($data) OR is_object($data) ) ) throw new Exception("Invalid data for XML serialization");

		if ( is_object($data) ) $data = $this->stdObj2array($data);

		require('XML.php');

		$xmlEngine = new XML();
		$xmlEngine->sourceArray = $data;

		$encoded = $xmlEngine->encode();

		switch ($prettfy) {
			
			case 'HTML':
				$return = htmlspecialchars($this->xml2txt($encoded), ENT_QUOTES);
				break;

			case 'TXT':
			case true:
				$return = $this->xml2txt($encoded);
				break;
			
			default:
				$return = $encoded;
				break;
		}

		return $return;

	}

	public final function toYAML($data) {

		if ( !( is_array($data) OR is_object($data) ) ) throw new Exception("Invalid data for XML serialization");

		if ( is_object($data) ) $data = $this->stdObj2array($data);

		require('Spyc.php');
		return Spyc::YAMLDump($data);

	}

	public final function toDUMP($data) {

		return var_export($data, true);

	}

	public final function toEXPORT($data) {

		return serialize($data);

	}

	/**
	 * Format xml string into txt string
	 * 
	 * @param	string	$xmlString	
	 * @return	string			
	 */
	private function xml2txt($xmlString) {
	
		$indent = '';

		$xmlString = str_replace("\n","",$xmlString);
		$xmlString = trim(preg_replace("/<\?[^>]+>/", "", $xmlString));
		$xmlString = preg_replace("/>([\s]+)<\//", "></", $xmlString);
		$xmlString = str_replace(">", ">\n", $xmlString);
		$xmlString = str_replace("<", "\n<", $xmlString);
		$xmlStringArray = explode("\n", $xmlString);

		$_xmlString = '';
		
		foreach($xmlStringArray as $k=>$tag){

			if ($tag == "") continue;
			
			if ($tag[0]=="<" AND $tag[1] != "/") {

				$_xmlString .= $indent.$tag."\n";
				$indent .= '  ';

			} elseif($tag[0]=="<" AND $tag[1] == "/") {

				$indent = substr($indent,0,strlen($indent)-2);
				$_xmlString .= (substr($_xmlString,strlen($_xmlString)-1)==">" || substr($_xmlString,strlen($_xmlString)-1)=="\n" ? $indent : '').$tag."\n";

			} else {

				$_xmlString = substr($_xmlString,0,strlen($_xmlString)-1);
				$_xmlString .= $tag;

			}

		}

		return $_xmlString;

	}

	/**
	 * Transform stdObject string into array 
	 * 
	 * @param	string/stdObject		$string			The string to decode
	 * @return	array									The decoded array
	 */
	private function stdObj2array($stdObj) {
	
		if(is_object($stdObj)) {

			$array = array();
			
			foreach($stdObj as $key=>$val){

				$array[$key] = stdObj2array($val);

			}

			return $array;

		}
		else return $stdObj;

	}

}

?>