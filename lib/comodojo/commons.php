<?php

/**
 * 
 * 
 * @package		Comodojo Spare Parts
 * @author		comodojo.org
 * @copyright	__COPYRIGHT__ comodojo.org (info@comodojo.org)
 * @version		__CURRENT_VERSION__
 * @license		GPL Version 3
 */

/**
 * Transform an array into json string
 * 
 * @param	array		$array			The array to encode
 * @return	string/json					The encoded string
 */
function array2json($array, $numeric_check=true) {
	
	if (!function_exists("json_encode")) {
		require('JSON.php');
		$json = new Services_JSON();
		$string = $json->encode($array);
	}
	else if ($numeric_check) {
		$string = json_encode($array, JSON_NUMERIC_CHECK);	
	}
	else {
		$string = json_encode($array);	
	}
	return $string;
	
}

/**
 * Transform json string into array 
 * 
 * @param	string/json		$string			The string to decode
 * @param	bool			$rawConversion	If true, DO NOT attempt to convert stdObj to array, instead return raw JSON2PHP data; default: false
 * @return	array							The decoded array
 */
function json2array($string, $raw = false) {
	
	if (!function_exists("json_decode")) {
		require('JSON.php');
		$json = new Services_JSON();
		$array = $json->decode($string);
		$return = $raw ? $array : stdObj2array($array);
	}
	else {
		$return = json_decode($string, !$raw);
	}
	return $return;
	
}

/**
 * Transform stdObject string into array 
 * 
 * @param	string/stdObject		$string			The string to decode
 * @return	array									The decoded array
 */
function stdObj2array($stdObj) {
	
	if(is_object($stdObj) OR is_array($stdObj)) {
		$array = array();
		foreach($stdObj as $key=>$val){
				$array[$key] = stdObj2array($val);
		}
		return $array;
	}
	else {
		 return $stdObj;
	}

}

/**
 * Transform an array into xml string 
 * 
 * @param	array		$array		The array to encode
 * @return	string/xml				The encoded string
 */
function array2xml($array) {
	
	require('XML.php');
	$xmlEngine = new XML();
	$xmlEngine->sourceArray = $array;
	return $xmlEngine->encode();
	
}

/**
 * Transform XML string into an array 
 * 
 * @param	string/json		$dataString		The string to decode
 * @return	array							The decoded array
 */
function xml2array($dataString) {
	
	require('XML.php');
	$xmlEngine = new XML();
	$xmlEngine->sourceString = $dataString;
	return $xmlEngine->decode();
	
}

/**
 * Transform an array into YAML string 
 * 
 * @param	array		$array		The array to encode
 * @return	string/YAML				The encoded string
 */
function array2yaml($array) {
	
	require('Spyc.php');
	return Spyc::YAMLDump($array);
	
}

/**
 * Transform YAML string into an array 
 * 
 * @param	string/json		$dataString		The string to decode
 * @return	array							The decoded array
 */
function yaml2array($dataString) {
	
	require('Spyc.php');
	return Spyc::YAMLLoadString($dataString);
	
}

/**
 * Transform hex data to binary data (reverse of bin2hex) 
 * 
 * @return	string
 */
if (!function_exists('hex2bin')) {
	function hex2bin($data) {
		$len = strlen($data);
		return pack("H" . $len, $data);
	}
}

/**
 * Generate random alphanumerical string
 * 
 * @param	int		$length	[optional] The random string length; default 128
 * @return	string	
 */
function random($length=128) {
	
	if ($length == 128) {
		$randNum = md5(uniqid(rand(), true), 0);
	}
	if ($length < 128) {
		$randNum = substr(md5(uniqid(rand(), true)), 0, $length);
	}
	else {
		$numString = (int)($length/128) + 1;
		$randNum = "";
		for ($i = 0; $i < $numString; $i++) {
			$randNum .=  md5(uniqid(rand(), true));
		}
		$randNum = substr($randNum, 0, $length);
	}
	return $randNum;
	
}

/**
 * Transform xml string into html-formatted one for easier reading
 * 
 * @param	string	$xmlString	
 * @return	string			
 */
function xml2html($xmlString) {
	return htmlspecialchars(xml2txt($xmlString), ENT_QUOTES);
}

/**
 * Format xml string into txt string
 * 
 * @param	string	$xmlString	
 * @return	string			
 */
function xml2txt($xmlString) {
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
		}
		elseif($tag[0]=="<" AND $tag[1] == "/") {
			$indent = substr($indent,0,strlen($indent)-2);
			$_xmlString .= (substr($_xmlString,strlen($_xmlString)-1)==">" || substr($_xmlString,strlen($_xmlString)-1)=="\n" ? $indent : '').$tag."\n";
		}
		else {
			$_xmlString = substr($_xmlString,0,strlen($_xmlString)-1);
			$_xmlString .= $tag;
		}
	}
	return $_xmlString;
}

/**
 * Check if required $parameters are matched by $attributes.
 * 
 * This function check not onlu the presence of parameters but also the
 * type and value (a mix of logical and math operators plus simple regex
 * match via preg_match()).
 * 
 * $parameters array should look like this:
 * <code>
 * Array(
 * 
 *         //simple match, only check the presence
 *         "name",
 *         
 *         //combined presence and type
 *         Array("phone","IS","NUMERIC"),
 *         
 *         //combined presence and value
 *         Array("seats",">=",1),
 *         
 *         //combined presence, type and value
 *         Array("confirm","IS","STRING"),
 *         Array("confirm","==","YES")
 * 
 * );
 * </code>
 * 
 * @param    array    $attributes        The attributes to match, in array format (see above)
 * @param    array    $parameters        The parameters to check, array-enclosed
 * 
 * @return    bool    true in case of match, false otherwise.
 * 
 */
function attributes_to_parameters_match($attributes, $parameters) {
    foreach ($parameters as $parameter) {
        if (is_array($parameter) AND @count($parameter) == 3) {
            if (value_coherence_check($parameter[0],$parameter[1],$parameter[2])) continue;
            else return false;
        }
        else {
            if (isset($attributes[$parameter])) continue;
            else return false;
        }
    }
    return true;
}

/**
 * check if $value is coherent with declared rules
 * 
 * @param    string    $value        The value that should match condition
 * @param    string    $condition    The condition to check
 * @param    string    $check        The value for the condition
 * 
 * @return    bool                True in case of condition match, false otherwise 
 */
function value_coherence_check($value, $condition, $check) {
    $to_return = true;
    switch (strtoupper($condition)) {
        case '=':
        case '!=':
            $to_return = $value == $check ? true : false;
        break;
        case '<=':
            $to_return = $value <= $check ? true : false;
        break;
        case '<':
            $to_return = $value <  $check ? true : false;
        break;
        case '>=':
            $to_return = $value >= $check ? true : false;
        break;
        case '>':
            $to_return = $value >  $check ? true : false;
        break;
        case 'IS':
        case '!IS':
            switch (strtoupper($check)) {
                case 'SCALAR':
                    $to_return = is_scalar($value);
                break;
                case 'STRING':
                    $to_return = is_string($value);
                break;
                case 'NUMERIC':
                    $to_return = is_numeric($value);
                break;
                case 'INTEGER':
                case 'INT':
                    if (is_numeric($value)) {
                        if(intval($value) === $value) {
                            $to_return = true;
                        }
                    }
                    else $to_return = false;
                break;
                case 'DOUBLE':
                case 'FLOAT':    
                    $to_return = is_double($value);
                break;
                default: return false; break;
            }
        break;
        case 'CONTAINS':
        case '!CONTAINS':
            $to_return = strstr($value, $check) === false ? false : true; 
        break;
        case 'STARTS':
        case '!STARTS':
            $to_return = substr($value, 0, sizeof($check)) == $check ? true : false;
        break;
        case 'ENDS':
        case '!ENDS':
            $to_return = substr($value, sizeof($value), -sizeof($check)) == $check ? true : false;
        break;
        case 'REGEXP':
        case '!REGEXP':
            $to_return = preg_match($check, $value);
        break;
        default: return false; break;
    }
    if (substr($condition,0,1) == '!') return !$to_return;
    else return $to_return;
}

?>