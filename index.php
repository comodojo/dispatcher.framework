<?php

/**
 * index.php
 * 
 * A simple URL router for REST Services dispatcher (package)
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

@(include('configs/router-config.php')) OR die ("system error");

$http = 'http' . ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 's' : '') . '://';
$uri = $_SERVER['REQUEST_URI'];
$uri = strpos($uri,'index.php') !== false ? preg_replace("/\/index.php(.*?)$/i","",$uri) : preg_replace("/\/\?.*/","",$uri);
$currentUrl = $http . $_SERVER['HTTP_HOST'] . $uri;
$currentUrl = str_replace('%20',' ',$currentUrl);

function goRoute($location) {
    header("Location: ".$location,true,302);
}

function goCloak($location, $cache=AUTO_CACHE, $ttl=DEFAULT_TTL) {
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    
    if (isset($_GET['transport'])) {
	if (strtoupper($_GET['transport']) == "XML") {
	    header('Content-type: application/xml');
	}
	else {
	    header('Content-type: application/json');
	}
    }
    elseif (strtoupper(DEFAULT_TRANSPORT) == "XML") {
	header('Content-type: application/xml');
    }
    else {
	header('Content-type: application/json');
    }
    
    if ($cache) {
	$result = getCache($location,$ttl);
	if ($result === false) {
	    $result = _goCurl($location);
	    setCache($location,$result);
	}
    }
    else {
	$result = _goCurl($location);
    }
    echo $result;
}

function _goCurl($location) {
    $ch = curl_init();
    if (!$ch) die ("router error");
    curl_setopt($ch, CURLOPT_URL, $location);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_USERAGENT,$_SERVER['HTTP_USER_AGENT']);
    curl_setopt($ch, CURLOPT_PORT, $_SERVER['SERVER_PORT']);
    return curl_exec($ch);
}

function getCache($request, $ttl) {
    $currentTime = strtotime('now');
    $bestBefore = $currentTime-$ttl;
    $requestTag = md5($request);
    if (is_readable(getcwd()."/cache/".$requestTag) AND @filemtime(getcwd()."/cache/".$requestTag) >= $bestBefore) return file_get_contents(getcwd()."/cache/".$requestTag);
    else return false;
}

function setCache($request, $data) {
    $requestTag = md5($request);
    $fh = fopen(getcwd()."/cache/".$requestTag, 'w');
    if (!$fh) return false;
    if (fwrite($fh, $data)) return false;
    fclose($fh);
    return true;
}


function cleanQueryString() {
    $qstring = false;
    foreach ($_GET as $key=>$value) {
        if (strtolower($key) == 'service') continue;
        elseif (!$qstring) $qstring='?'.$key.'='.$value;
        else $qstring.='&'.$key.'='.$value;
    }
    return $qstring;
}

if (!isset($_GET['service'])) die ("unspecified service");
elseif (!isset($registered_services[$_GET['service']]) AND !AUTO_ROUTE) die ("unknown service");
elseif (!isset($registered_services[$_GET['service']]) AND AUTO_ROUTE) {
    if (is_readable("services/".$_GET['service'].".php")) {
	$location = $currentUrl."/services/".$_GET['service'].".php".cleanQueryString();
	if (DEFAULT_POLICY == 'CLOAK') goCloak($location);
	else goRoute($location);
    }
    else die ("unknown service");
}
else{
    
    if (!isset($registered_services[$_GET['service']]["target"]) OR !isset($registered_services[$_GET['service']]["policy"])) die ("malformed service");
    
    $location = $currentUrl."/services/".$registered_services[$_GET['service']]["target"].cleanQueryString();
    
    if ((!$registered_services[$_GET['service']]["policy"] ? DEFAULT_POLICY : $registered_services[$_GET['service']]["policy"]) == 'CLOAK') {
	goCloak(
	    $location,
	    isset($registered_services[$_GET['service']]['cache']) ? $registered_services[$_GET['service']]['cache'] : AUTO_CACHE,
	    isset($registered_services[$_GET['service']]['ttl']) ? $registered_services[$_GET['service']]['ttl'] : DEFAULT_TTL
	);
    }
    else goRoute($location);
    
}

exit;

?>