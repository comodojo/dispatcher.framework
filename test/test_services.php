<?php

/* test_services.php
* 
* Test example services included in package using server side requests.
* 
* @package	Comodojo Spare Parts
* @author	comodojo.org
* @copyright	2012 comodojo.org (info@comodojo.org)
* @version	*_BUILD_VERSION_*
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

function go_curl($method, $location, $attributes=false, $origin=false) {
    
    $http = 'http' . ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 's' : '') . '://';
    $uri = $_SERVER['REQUEST_URI'];
    $uri = strpos($uri,'test_services.php') !== false ? preg_replace("/\/test_services.php(.*?)$/i","",$uri) : preg_replace("/\/\?.*/","",$uri);
    $currentUrl = $http . $_SERVER['HTTP_HOST'] . $uri;
    $currentUrl = str_replace('%20',' ',$currentUrl);
    $currentUrl .= '/../services/';
    
    $ch = curl_init();
    if (!$ch) die ('curl error');
	
    curl_setopt_array($ch,Array(
	CURLOPT_RETURNTRANSFER	=>	1,
	CURLOPT_HEADER 		=>	0,
	//CURLOPT_HEADERFUNCTION	=>	'read_curl_header',
	CURLOPT_HTTPHEADER	=>	array("Expect:"),
	CURLOPT_TIMEOUT		=>	30,
	CURLOPT_USERAGENT	=>	'Comodojo Test UA',
	CURLOPT_PORT		=>	80,
	CURLOPT_CUSTOMREQUEST	=>	$method,
	CURLOPT_FOLLOWLOCATION	=>	1
    ));
    
    if ($origin != false) curl_setopt($ch, CURLOPT_HTTPHEADER, array('ORIGIN: '.$origin));
    
    switch (strtoupper($method)) {
	case 'PUT':
	case 'DELETE':
	    curl_setopt($ch, CURLOPT_URL, $currentUrl.$location);
	    if ($attributes != false) curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($attributes));
	break;
	case 'POST':
	    curl_setopt($ch, CURLOPT_URL, $currentUrl.$location);
	    if ($attributes != false) curl_setopt($ch, CURLOPT_POSTFIELDS, $attributes);
	break;
	//FALLBACK to HTTP-GET
	default:
	    curl_setopt($ch, CURLOPT_URL, $currentUrl.$location.(!$attributes ? '' : '?'.http_build_query($attributes)));
	break;
    }
    
    $response			= curl_exec($ch);
    $responseStatus		= curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $responseTime		= curl_getinfo($ch, CURLINFO_TOTAL_TIME);
    $responseUrl		= curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    $responseRedirectTime	= curl_getinfo($ch, CURLINFO_REDIRECT_TIME);
    
    echo '<div class="info">';
    echo '<p>HTTP '.$method." - Server response: <strong>".$responseStatus."</strong> - Request total time (secs): ".$responseTime.' - Redirect time (secs): '.$responseRedirectTime.'</p>';
    echo '<pre>Original URL: '.$currentUrl.$location.'</pre>';
    echo '<pre>Response URL: '.$responseUrl.'</pre><br/>';
    echo '<code>'.$response.'</code><br/>';
    echo '</div>';

}
    
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>Comodojo simpleDataRestDispatcher test scripts</title>
	<link rel="stylesheet" type="text/css" href="test.css" />
</head>

<body>

	<div id="testContainer">
	
		<div id="testHeader">
	
			<img src="../others/logo.png" alt="The Comodojo Logo"/>
	
		</div>
	
		<div id="testContent">
			<h1>This page will tests different example services.</h1>
			<h2>Example hello world (GET,PUT,POST,DELETE)</h2>
			<?php go_curl('GET','example_hello_world.php',Array('to'=>'Comodojo-GET')); ?>
			<?php go_curl('PUT','example_hello_world.php',Array('to'=>'Comodojo-PUT')); ?>
			<?php go_curl('POST','example_hello_world.php',Array('to'=>'Comodojo-POST')); ?>
			<?php go_curl('DELETE','example_hello_world.php',Array('to'=>'Comodojo-DELETE')); ?>
			<h2>Example hello world (POST:200,GET/PUT/DELETE:501)</h2>
			<?php go_curl('GET','example_hello_world_post_only.php',Array('to'=>'Comodojo-GET')); ?>
			<?php go_curl('PUT','example_hello_world_post_only.php',Array('to'=>'Comodojo-PUT')); ?>
			<?php go_curl('POST','example_hello_world_post_only.php',Array('to'=>'Comodojo-POST')); ?>
			<?php go_curl('DELETE','example_hello_world_post_only.php',Array('to'=>'Comodojo-DELETE')); ?>
			<h2>Example hello world CORS (403 - Origin not allowed)</h2>
			<?php go_curl('GET','example_hello_world_cors_ac.php',Array('to'=>'Comodojo-GET')); ?>
			<h2>Example hello world CORS (200 - Origin = 'comodojo.org' simulation)</h2>
			<?php go_curl('GET','example_hello_world_cors_ac.php',Array('to'=>'Comodojo-GET'),'comodojo.org'); ?>
		</div>
		
		<div id="testFooter">
		
			<p>&copy; 2013 comodojo.org | <a href="http://www.comodojo.org" target="_blank">comodojo.org</a> | All Rights Reserved | Distributed under <a href="http://www.gnu.org/copyleft/gpl.html" target="_blank">GPL V3</a> terms</p>
		
		</div>
	
	</div>

</div>
</body>
</html>