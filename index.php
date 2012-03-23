<?php

/**
 * index.php
 * 
 * A simple URL router for REST Services dispatcher (package)
 * 
 * @package	Comodojo Spare Parts
 * @author	comodojo.org
 * @copyright	2012 comodojo.org (info@comodojo.org)
 * @version	{*_CURRENT_VERSION_*}
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

@(include('configs/router-config.php')) OR die ("system error");

/**
 * The router.
 *
 * It receive requests and redirect them to services; it don't use Apache
 * .htaccess expressions in order to maintain better compatibility and keep
 * routing table clean.
 */
class router {
    
    private $currentUrl = false;
    private $currentPath = false;
    private $bestBefore = 'Mon, 26 Jul 1997 05:00:00 GMT';
    private $maxAge = false;
    private $contentType = false;
    private $ch = false;
    
    private $error_patterns = Array(
	'JSON'	=>	"{success: false, result: '{0}'}",
	'XML'	=>	"<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<content><success></success><result>{0}</result></content>"
    );
    
    /**
     * Route request with a 302 message back
     *
     * @param	STRING	$location	The location to route request to
     */
    private function go_route($location) {
	header("Location: ".$location,true,302);
    }
    
    /**
     * Cloak request, with or without cache
     *
     * @param	STRING	$location	The location to route request to
     * @param	STRING	$cache		The cache type
     * @param	INT	$ttl		The cache time to live
     */
     private function go_cloak($location, $cache=AUTO_CACHE, $ttl=DEFAULT_TTL) {
	if (!$cache) {
	    $result = $this->go_curl($location);
	}
	elseif (strtoupper($cache) == "BOTH") {
	    $result = $this->get_cache($location,$ttl,true);
	    if ($result === false) {
		$result = $this->go_curl($location);
		$this->set_cache($location,$result);
	    }
	}
	elseif (strtoupper($cache) == "SERVER") {
	    $result = $this->get_cache($location,$ttl,false);
	    if ($result === false) {
		$result = $this->go_curl($location);
		$this->set_cache($location,$result);
	    }
	}
	else {
	    $this->maxAge = $ttl;
	    //$this->bestBefore = http_date(strtotime('now') + $ttl);
	    $this->bestBefore = gmdate("D, d M Y H:i:s", time() + $ttl) . " GMT";
	    $result = $this->go_curl($location);
	}
	return $result;
    }
    
    /**
     * Cloak request using internal curl.
     *
     * @param	STRING	$location	The location to cloak
     */
    private function go_curl($location) {
	$this->ch = curl_init();
	if (!$this->ch) die ($this->go_error('router error'));
	curl_setopt($this->ch, CURLOPT_URL, $location);
	curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($this->ch, CURLOPT_TIMEOUT, 30);
	curl_setopt($this->ch, CURLOPT_USERAGENT,$_SERVER['HTTP_USER_AGENT']);
	curl_setopt($this->ch, CURLOPT_PORT, $_SERVER['SERVER_PORT']);
	return curl_exec($this->ch);
    }
    
    /**
     * Get error message according to current transport
     */
    private function go_error($message) {
	return str_replace('{0}',$message, $this->error_patterns[strtoupper($this->contentType)]);
    }
    
    /**
     * Get server cache and, if valid cache, compute client cache params
     *
     * @param	STRING	$request	The request to tag
     * @param	INT	$ttl		The cache time to live
     * @param	BOOL	$client		If true, compute client cache params
     */
    private function get_cache($request, $ttl, $client) {
	$currentTime = strtotime('now');
	$last_time_limit = $currentTime-$ttl;
	$requestTag = md5($request);
	if (is_readable($this->currentPath."/cache/".$requestTag) AND @filemtime($this->currentPath."/cache/".$requestTag) >= $last_time_limit) {
	    if ($client) {
		$cache_time = filemtime($this->currentPath."/cache/".$requestTag);
		$this->maxAge = $cache_time + $ttl - $currentTime;
		//$this->bestBefore = http_date($cache_time + $ttl);
		$this->bestBefore = gmdate("D, d M Y H:i:s", $cache_time + $ttl) . " GMT";
	    }
	    return file_get_contents($this->currentPath."/cache/".$requestTag);
	}
	else return false;
    }

    /**
     * Set server cache
     *
     * @param	STRING	$request	The request to tag
     * @param	STRING	$data		The data to return
     */
    function set_cache($request, $data) {
	$requestTag = md5($request);
	$fh = fopen($this->currentPath."/cache/".$requestTag, 'w');
	if (!$fh) return false;
	if (fwrite($fh, $data)) return false;
	fclose($fh);
	return true;
    }
      
    /**
     * Set header content type and cache
     */
    private function set_header() {
	header('Cache-Control: ' . (!$this->maxAge ? 'no-cache, must-revalidate' : 'max-age='.$this->maxAge.', must-revalidate') );
	header('Expires: '.$this->bestBefore);
	header('Content-type: application/'.$this->contentType);
    }
    
    /**
     * Clean query string from service tag and return it.
     */
    private function clean_query_string() {
	$qstring = false;
	foreach ($_GET as $key=>$value) {
	    if (strtolower($key) == 'service') continue;
	    elseif (!$qstring) $qstring='?'.$key.'='.$value;
	    else $qstring.='&'.$key.'='.$value;
	}
	return $qstring;
    }
    
    /**
     * Constructor: setup basic variables then apply routing logic.
     */
    public function __construct() {
	
	global $registered_services;
	
	//setup basic variables
	$http = 'http' . ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 's' : '') . '://';
	$uri = $_SERVER['REQUEST_URI'];
	$uri = strpos($uri,'index.php') !== false ? preg_replace("/\/index.php(.*?)$/i","",$uri) : preg_replace("/\/\?.*/","",$uri);
	$currentUrl = $http . $_SERVER['HTTP_HOST'] . $uri;
	$this->currentUrl = str_replace('%20',' ',$currentUrl);
	$this->currentPath = getcwd();
	$toReturn = false;
	
	//setup transport
	if (isset($_GET['transport']) AND (@strtoupper($_GET['transport']) == "XML" OR @strtoupper($_GET['transport']) == "JSON") ) $this->contentType = strtolower($_GET['transport']);
	else $this->contentType = strtolower(DEFAULT_TRANSPORT);
	
	/***routing logic***/
	//if no service, die immediately
	if (!isset($_GET['service'])) die ($this->go_error('unspecified service'));
	//if service not in routing table and autoroute disabled, die immediately
	elseif (!isset($registered_services[$_GET['service']]) AND !AUTO_ROUTE) die ($this->go_error("unknown service"));
	//if service not in routing table and autoroute enabled, process request
	//with default cache params
	elseif (
	    (!isset($registered_services[$_GET['service']]) AND AUTO_ROUTE)
		OR
	    (isset($registered_services[$_GET['service']])
		AND (!isset($registered_services[$_GET['service']]["target"]) OR !isset($registered_services[$_GET['service']]["policy"]))
	    )
	){
	    if (is_readable("services/".$_GET['service'].".php")) {
		$location = $currentUrl."/services/".$_GET['service'].".php".$this->clean_query_string();
		if (strtoupper(DEFAULT_POLICY) == 'CLOAK') $toReturn = $this->go_cloak($location);
		else $this->go_route($location);
	    }
	    else die ($this->go_error("unknown service"));
	}
	else{
	    $location = $currentUrl."/services/".$registered_services[$_GET['service']]["target"].$this->clean_query_string();
	    if ($registered_services[$_GET['service']]["policy"] == 'CLOAK') {
		$toReturn = $this->go_cloak(
		    $location,
		    isset($registered_services[$_GET['service']]['cache']) ? $registered_services[$_GET['service']]['cache'] : AUTO_CACHE,
		    isset($registered_services[$_GET['service']]['ttl']) ? $registered_services[$_GET['service']]['ttl'] : DEFAULT_TTL
		);
	    }
	    else $this->go_route($location);
	}
	$this->set_header();
	echo $toReturn;
	exit;
    }
    
    public function __destruct() {
	if ($this->ch !== false) curl_close($this->ch);
    }
    
}

$router = new router();

?>