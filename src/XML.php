<?php namespace Comodojo\Dispatcher;

use \SimpleXMLElement;
use \Comodojo\Exception\XMLException;

/** 
 * XML data transformation class
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
class XML {

    /**
     * XML version
     * 
     * @var string
     */
    private $version = "1.0";
    
    /**
     * XML encoding (header)
     * 
     * @var string
     */
    private $encoding = null;
    
    /**
     * XML parent element (XML body)
     * 
     * @var string  
     */
    private $parent = "content";
    
    /**
     * Include/exclude header in XML construction
     * 
     * @var bool
     */
    private $includeHeader = true;
    
    /**
     * Controls whether case-folding is enabled for XML parser
     * 
     * @var integer
     */
    private $caseFolding = 0;
    
    /**
     * Whether XML parser should skip values consisting of whitespace characters. 
     * 
     * @var integer
     */
    private $skipWhite = 1;
    
    /**
     * Header string to prepend to xml data
     * 
     * @var string
     */
    private $header_string = '<?xml version="__VERSION__" encoding="__ENCODING__"?>';

    /**
     * Internal pointer to SimpleXMLElement
     * 
     */
    private $ObjectXML;

    /**
     * Internal pointer to xml_parser
     * 
     */
    private $parser;

    /**
     * Internal pointer to parsed xml structs
     * 
     * @var array
     */
    private $struct = array();

    /**
     * Number of elements retrieved from parser
     * 
     * @var integer
     */
    private $counter;
    
    /**
     * XML class constructor
     */
    final public function __construct() {

        $this->encoding = defined('DISPATCHER_DEFAULT_ENCODING') ? DISPATCHER_DEFAULT_ENCODING : "UTF-8";

    }

    /**
     * Set if header should be included in xml encoding 
     * 
     * @param bool $bool
     *
     * @return Object $this
     */
    final public function setIncludeHeader($bool) {

        $this->includeHeader = filter_var($bool, FILTER_VALIDATE_BOOLEAN);

        return $this;

    }

    /**
     * Enable case-folding for XML parser
     * 
     * @param int $int
     *
     * @return Object $this
     */
    final public function setCaseFolding($int) {

        $this->caseFolding = filter_var($int, FILTER_VALIDATE_INT);

        return $this;

    }

    /**
     * Enable whitespace characters skip for XML parser
     * 
     * @param int $int
     *
     * @return Object $this
     */
    final public function setSkipWhite($int) {

        $this->skipWhite = filter_var($int, FILTER_VALIDATE_INT);

        return $this;

    }

    /**
     * Set xml parser and encoder encoding
     * 
     * @param string $encoding
     *
     * @return Object $this
     */
    final public function setEncoding($encoding) {

        $encoding = strtoupper($encoding);

        $this->encoding = in_array($encoding, mb_list_encodings()) ? $encoding : $this->encoding;

        return $this;

    }

    /**
     * Get XML header
     *
     * @return  string
     */
    final public function getHeader() {

        return str_replace(array("__VERSION__", "__ENCODING__"), array($this->version, $this->encoding), $this->header_string);

    }
    
    /**
     * Get XML header
     *
     * @return  string
     */
    final public function getParent() {

        return "<".$this->parent."></".$this->parent.">";

    }

    /**
     * Encode array to XML string 
     * 
     * @return string   
     */
    public function encode(array $data) {

        $structure = ($this->includeHeader ? $this->getHeader() : "") . $this->getParent();
        
        $this->ObjectXML = new SimpleXMLElement($structure);
        
        $this->pushElement($this->ObjectXML, $data);
        
        $xml = $this->ObjectXML->asXML();

        if ( $xml === false ) throw new XMLException("Error formatting object");
        
        return $xml;
        
    }

    /**
     * encode XML string into array 
     * 
     * @return array    
     */
    public function decode(string $xml, $encoding=null) {

        $this->parser = is_null($encoding) ? xml_parser_create() : xml_parser_create($encoding);

        xml_parser_set_option($this->parser,XML_OPTION_TARGET_ENCODING,$this->encoding);
        xml_parser_set_option($this->parser,XML_OPTION_CASE_FOLDING,$this->caseFolding);
        xml_parser_set_option($this->parser,XML_OPTION_SKIP_WHITE,$this->skipWhite);
        
        $parser_structs = xml_parse_into_struct($this->parser, $xml, $this->struct, $index);
        
        if ( $parser_structs === 0 ) throw new XMLException("Failed to parse xml data into structs");
        
        $this->counter = count($this->struct);

        array_walk($this->struct, array($this, 'sanitizeKeys'));
        
        $this->free();
        
        $t = $this->getTree();
        
        return $t; 
        
    }
    
    /**
     * convert keys created from XML::encode() into array numeric keys
     * 
     */
    private function sanitizeKeys($input, $key) {

        $key_value = explode("_",$this->struct[$key]["tag"]);

        if ( sizeof($key_value) == 2 AND $key_value[0] == "KEY" AND is_numeric($key_value[1]) ) {

            $this->struct[$key]["tag"] = intval($key_value[1]);

        }

    }
    
    /**
     * Free xml parser and unset resource
     */
    private function free() {

        if( isset($this->parser) AND @is_resource($this->parser) ) {

            xml_parser_free($this->parser);

            unset($this->parser);

        }
        
    }
    
    /**
     * Get composed tree into array and unset resource
     *
     * @return array
     */
    private function getTree() {

        $i = 0;

        $tree = array();

        $key = $this->struct[$i]["tag"];

        $attributes = isset($this->struct[$i]["attributes"]) ? $this->struct[$i]["attributes"] : "";

        $value = isset($this->struct[$i]["value"]) ? $this->struct[$i]["value"] : "";

        $child = $this->getChilds($i);

        $tree = $this->addNode($tree, $key, $attributes, $child, $value);

        unset($this->struct);

        return $tree;

    }

    /**
     * Get element childs (if any)
     *
     * @return array
     */
    private function getChilds(&$i) {

        $children = array();

        while (++$i < $this->counter) {

            $tagname = $this->struct[$i]["tag"];

            $attributes = isset($this->struct[$i]["attributes"]) ? $this->struct[$i]["attributes"] : "";

            $value = isset($this->struct[$i]["value"]) ? $this->struct[$i]["value"] : "";

            switch($this->struct[$i]["type"]) {

                case "open":
                $child = $this->getChilds($i);
                $children = $this->addNode($children, $tagname, $attributes, $child, $value);
                break;
                
                case "complete":
                $child = "";
                $children = $this->addNode($children, $tagname, $attributes, $child, $value);
                break;
                
                case "close":
                return $children;
                break;

            }

        }

    }

    /**
     * Add node to array
     *
     */
    private function addNode($target, $key, $attributes, $child, $value) {
        
        if ( !isset($target[$key]) ) {
            
            if ($child != "") $target[$key] = $child;

            if ($attributes != "") foreach($attributes as $akey => $avalue) $target[$key][$akey] = $avalue;

            else if ( $value != "" ) $target[$key] = $value;
            
        } else {

            if (!isset($target[$key][0])) {
                
                $oldval = $target[$key];
                $target[$key] = array();
                $target[$key][0] = $oldval;
                $index=1;

            }
            else {

                $index = count($target[$key]);

            }

            if ( $child != "" ) $target[$key][$index] = $child;

            if ( $attributes != "" ) foreach($attributes as $akey => $avalue) $target[$key][$index][$akey] = $avalue;

            elseif ( $value != "" ) $target[$key][$index] = $value;
        }

        return $target;

    }

    /**
     * Push key/value element into SimpleXMLElement
     *
     * @param SimpleXMLElement $ObjectXML
     * @param array            $data
     */
    private function pushElement($ObjectXML, $data) {

        foreach ($data as $key => $value) {

            if ( is_array($value) ) $this->pushElement($ObjectXML->addChild(is_numeric($key) ? 'KEY_'.$key : $key), $value);

            else $ObjectXML->addChild(is_numeric($key) ? 'KEY_'.$key : $key, mb_convert_encoding($value, $this->encoding));

        }

    }
    
}