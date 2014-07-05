<?php namespace comodojo\Dispatcher;

/**
 * serialization class for dispatcher
 * 
 * @package		Comodojo dispatcher (Spare Parts)
 * @author		comodojo <info@comodojo.org>
 * @license		GPL-3.0+
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

	/**
	 * Convert Array to JSON using PHP json_encode func
	 *
	 * Second parameters may contain flags passed to encoder
	 *
	 * @param 	array 	$data 	Data to convert
	 * @param 	integer $flags 	Flags for json_encode
	 *
	 * @return 	string 	JSON encoded data
	 */
	public final function toJSON($data, $flags=NULL) {

		if ( !( is_array($data) OR is_object($data) ) ) throw new Exception("Invalid data for JSON serialization");

		return json_encode($data, $flags);

	}

	/**
	 * Convert Array to XML using comodojo XML class
	 *
	 * Second parameters, if true, will try to prettify xml output
	 *
	 * @param 	array 	$data 		Data to convert
	 * @param 	string 	$prettify	HTML || TXT (alias true) || false
	 *
	 * @return 	string 	XML encoded data
	 */
	public final function toXML($data, $prettify=false) {

		if ( !( is_array($data) OR is_object($data) ) ) throw new Exception("Invalid data for XML serialization");

		if ( is_object($data) ) $data = $this->stdObj2array($data);

		$xmlEngine = new XML();
		$xmlEngine->sourceArray = $data;

		$encoded = $xmlEngine->encode();

		switch ($prettify) {
			
			case 'HTML':
			case 'html':
				$return = htmlspecialchars($this->xml2txt($encoded), ENT_QUOTES);
				break;

			case 'TXT':
			case 'txt':
			case true:
				$return = $this->xml2txt($encoded);
				break;
			
			default:
				$return = $encoded;
				break;
		}

		return $return;

	}

	/**
	 * Convert Array to YAML using Spyc converter
	 *
	 * @param 	array 	$data 		Data to convert
	 *
	 * @return 	string 	YAML encoded data
	 */
	public final function toYAML($data) {

		if ( !( is_array($data) OR is_object($data) ) ) throw new Exception("Invalid data for XML serialization");

		if ( is_object($data) ) $data = $this->stdObj2array($data);

		return \Spyc::YAMLDump($data);

	}

	/**
	 * Convert data (almost any kind) to human readable export using var_export
	 *
	 * @param 	serial|array|object 	$data 		Data to convert
	 *
	 * @return 	string 	exported data
	 */
	public final function toDUMP($data) {

		return var_export($data, true);

	}

	/**
	 * Convert data (almost any kind) to PHP machine readable export using serialize
	 *
	 * @param 	serial|array|object 	$data 		Data to convert
	 *
	 * @return 	string 	serialized data
	 */
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
	 * Transform stdObject into array 
	 * 
	 * @param	stdObject	$stdObj
	 * @return	array		The decoded array
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