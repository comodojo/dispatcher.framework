<?php

/** 
 * XML.php
 * 
 * XML data transformation class;
 *
 * This package provides a simple encoder and decoder for XML.
 *
 * Encorder will transform input array in simple XML notation; altering
 * class parameters it is possible to select output characteristics, such as
 * encoding, xml version, ...
 *
 * Decoder will transform a source XML string into n-dimensional array.
 *
 * @package	Comodojo Spare Parts
 * @author	comodojo.org
 * @copyright	2012 comodojo.org (info@comodojo.org)
 * @version	1.1
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
 *
 */

class XML {
	
	/**
	 * Source array to transform
	 * 
	 * @var		string
	 * @default	false	(will return error)
	 */
	public $sourceArray = false;
	
	/**
	 * XML version (header)
	 * 
	 * @var 	string
	 * @default	1.0	
	 */
	public $xmlVersion = "1.0";
	
	/**
	 * XML encoding (header)
	 * 
	 * @var 	string
	 * @default	UTF-8	
	 */
	public $xmlEncoding = DEFAULT_ENCODING;
	
	/**
	 * XML parent element (XML body)
	 * 
	 * @var 	string
	 * @default	content	
	 */
	public $parentElement = "content";
	
	/**
	 * should XML output string include header?
	 * 
	 * @var 	bool
	 * @default	true	
	 */
	public $includeHeader = true;
	
	/**
	 * XML source string to transform (in array)
	 * 
	 * @var 	string
	 * @default	false	(will return error)
	 */
	public $sourceString = false;
	
	/**
	 * XML source encoding 
	 * 
	 * @var 	string
	 * @default	UTF-8	
	 */
	public $sourceEncoding = DEFAULT_ENCODING;
	
	/**
	 * XML destination encoding 
	 * 
	 * @var 	string
	 * @default	UTF-8	
	 */
	public $destinationEncoding = DEFAULT_ENCODING;
	
	private $_xmlobj;
	private $_parser;
	private $_counter;
	private $_struct = array();
	private $_unique = array();
	private $result = null;
	
	/**
	 * encode array in XML string 
	 * 
	 * @return string	
	 */
	public function encode() {

		if (!$this->sourceArray OR !is_array($this->sourceArray)) {
			throw new Exception('No source array!');
			return false;
		}
		
		$xmltext = ($this->includeHeader ? $this->_getHeader() : "") . /*($this->startFromParent ?*/ $this->_getParent() /*: "")*/; 
		
		$this->_xmlobj = new SimpleXMLElement($xmltext);
	
		$this->_encode($this->_xmlobj, $this->sourceArray);
		
		return $this->_xmlobj->asXML();
		
	}
	
	/**
	 * decode XML string in array 
	 * 
	 * @return array	
	 */
	public function decode() {
		
		if (!$this->sourceString OR $this->sourceString == "") {
			throw new Exception('No source string!');
			return false;
		}
		
		if ($this->sourceEncoding) {
			$this->_parser = xml_parser_create('');
		}
		else {
			$this->_parser = xml_parser_create($this->sourceEncoding);
		}
		if ($this->destinationEncoding !== false) xml_parser_set_option($this->_parser,XML_OPTION_TARGET_ENCODING,$this->destinationEncoding);
		
		xml_parser_set_option($this->_parser,XML_OPTION_CASE_FOLDING,0);
		xml_parser_set_option($this->_parser,XML_OPTION_SKIP_WHITE,1);
		
		//$pointer = &$this->_struct;
		
		xml_parse_into_struct($this->_parser,$this->sourceString,$this->_struct, $index);
		
		$this->_counter = count($this->_struct);
		
		array_walk($this->_struct, array ($this, '_sanitizeKeys'));
		
		//print_r($this->_struct);
		
		$this->_free();
		
		$t = $this->_getTree();
		
		return $t; 
		
	}

	private function _getHeader() {
		return "<?xml version=\"$this->xmlVersion\" encoding=\"$this->xmlEncoding\"?>";
	}
	
	private function _getParent() {
		return "<".$this->parentElement."></".$this->parentElement.">";
	}
	
	private function _encode($xmlObj, $sourceArray) {
		foreach ($sourceArray as $k => $v) {
			if (is_array($v)) {
				$this->_encode($xmlObj->addChild(is_numeric($k) ? 'KEY_'.$k : $k), $v);
			}
			else {
				$xmlObj->addChild(is_numeric($k) ? 'KEY_'.$k : $k, $v);
			}
		}
		return $xmlObj;
	}

	private function _sanitizeKeys($input, $key) {
		$kVal = explode("_",$this->_struct[$key]["tag"]);
		if ($kVal[0] == "KEY") {
			//error_log('Key '.$key.' seems masquerated ('.$this->_struct[$key]["tag"].'); will become: '.$kVal[1]);
			$this->_struct[$key]["tag"] = intval($kVal[1]);
		}
	}
	
	private function _free() {
		if( isset($this->_parser) AND is_resource($this->_parser)) {
			xml_parser_free($this->_parser);
			unset($this->parser);
		}
	}
	
	private function _getTree() {
		$i = 0;
		$tree = array();
		$_key = $this->_struct[$i]["tag"];
		$_attributes = isset($this->_struct[$i]["attributes"]) ? $this->_struct[$i]["attributes"] : "";
		$_value = isset($this->_struct[$i]["value"]) ? $this->_struct[$i]["value"] : "";
		$_child = $this->_getChild($i);
		$tree = $this->_addNode($tree, $_key, $_attributes, $_child, $_value);
		unset($this->struct);
		return $tree;
	}

	private function _getChild(&$i) {
		$_children = array();
		while (++$i < $this->_counter) {
			$_tagname = $this->_struct[$i]["tag"];
			$_attributes = isset($this->_struct[$i]["attributes"]) ? $this->_struct[$i]["attributes"] : "";
			$_value = isset($this->_struct[$i]["value"]) ? $this->_struct[$i]["value"] : "";
			switch($this->_struct[$i]["type"]) {
				case "open":
					$_child = $this->_getChild($i);
					$_children = $this->_addNode($_children,$_tagname,$_attributes,$_child,$_value);
					break;
				case "complete":
					$_child = "";
					$_children = $this->_addNode($_children,$_tagname,$_attributes,$_child,$_value);
					break;
				case "close":
					return $_children;
					break;
			}
		}
	}

	private function _addNode($target, $key, $attributes, $child, $value) {
		if (!isset($target[$key])) {
			/*if (in_array($key,$this->_unique)) {
				if ($child != "") {
					 $target[$key][0] = $child;
				}
				if ($attributes != "") {
					foreach($attributes as $k => $v) $target[$key][0][$k] = $v;
				}
				elseif ($value != "") $target[$key][0] = $value;
				$index=1;
			}
			else {*/
				if ($child != "") {
					$target[$key] = $child;
				}
				if ($attributes != ""){
					foreach($attributes as $k => $v) $target[$key][$k] = $v;
				}
				elseif ($value != "") $target[$key] = $value;
			//}
		}
		else {
			if (!isset($target[$key][0])) {
				$oldval = $target[$key];
				$target[$key] = array();
				$target[$key][0] = $oldval;
				$index=1;
			}
			else {
				$index = count($target[$key]);
			}
			if ($child != "") $target[$key][$index] = $child;
			if ($attributes != "") foreach($attributes as $k => $v) $target[$key][$index][$k] = $v;
			elseif ($value != "") $target[$key][$index] = $value;
		}
		return $target;
	}
	
}

/**
 * Sanity check for CoMoDojo loader
 * 
 * @define function loadHelper_XML
 */
function loadHelper_XML() {
	return false;
}

?>