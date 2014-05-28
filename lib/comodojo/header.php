<?php

	/**
 * Set header accoding to $params.
 * 
 * $params could include:
 * + statusCode - the status code to return, in numerical format
 * + contentType
 * + charset
 * + ttl
 * + location
 * + lastModified
 * + implementedMethods
 * + allowedMethods
 * + contentDisposition
 * + filename
 * + accessControlAllowOrigin
 */
function set_header ($params, $contentLength=0) {

	$statusCode		= isset($params['statusCode']) ? $params['statusCode'] : 200;
	
	$contentType	= isset($params['contentType']) ? $params['contentType'] : false;
	$charset		= isset($params['charset']) ? $params['charset'] : false; //COMODOJO_DEFAULT_ENCODING;
	
	$ttl			= isset($params['ttl']) ? $params['ttl'] : null;
	
	$location		= isset($params['location']) ? $params['location'] : false;
	
	$lastModified	= isset($params['lastModified']) ? $params['lastModified'] : false;
	
	$implementedMethods = isset($params['implementedMethods']) ? $params['implementedMethods'] : false;
	$allowedMethods		= isset($params['allowedMethods']) ? $params['allowedMethods'] : false;
	
	$contentDisposition = isset($params['contentDisposition']) ? $params['contentDisposition'] : false;

	$contentRange = isset($params['contentRange']) ? $params['contentRange'] : false;

	$filename 		= isset($params['filename']) ? $params['filename'] : false;
	
	$accessControlAllowOrigin = isset($params['accessControlAllowOrigin']) ? $params['accessControlAllowOrigin'] : false;

	//not strictly needed but may cause problems if omitted in some XHR request
	if ($accessControlAllowOrigin == '*') header('Access-Control-Allow-Origin: *');
	elseif ($accessControlAllowOrigin != false) header('Access-Control-Allow-Origin: '.$accessControlAllowOrigin);
	else {}
		
	switch ($statusCode) {
		case 200: //OK
			if ($ttl > 0) {
				header('Cache-Control: max-age='.$ttl.', must-revalidate');
				header('Expires: '.gmdate("D, d M Y H:i:s", time() + $ttl)." GMT");
			}
			elseif ($ttl == 0) {
				header('Cache-Control: no-cache, must-revalidate');
				header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
			}
			else {
				//null
			}
			if ($contentType !== false) header('Content-type: '.strtolower($contentType).($charset != false ? '; charset='.$charset : ''));
			if ($contentLength !== 0) header('Content-Length: '.$contentLength,true);
			if ($contentDisposition !== false) header('Content-Disposition: '.$contentDisposition.';'.( !$filename ? '' : (' filename="'.$filename.'"') ),true);
			if ($contentRange !== false) header('Content-Range: '.$contentRange,true);
		break;
		case 202: //Accepted
			//PLEASE NOTE: according to HTTP/1.1, 202 header SHOULD HAVE status description in body... just in case
			header($_SERVER["SERVER_PROTOCOL"].' 202 Accepted');
			header('Status: 202 Accepted');
			if ($contentLength !== 0) header('Content-Length: '.$contentLength,true);
		break;
		case 204: //OK - No Content
			header($_SERVER["SERVER_PROTOCOL"].' 204 No Content');
			header('Status: 204 No Content');
			header('Content-Length: 0',true);
		break;
		case 201: //Created
		case 301: //Moved Permanent
		case 302: //Found
		case 303: //See Other
		case 307: //Temporary Redirect
			header("Location: ".$location,true,$statusCode);
			header('Content-Length: '.$contentLength,true); //is it needed?
		break;
		case 304: //Not Modified
			if (!$lastModified) header($_SERVER["SERVER_PROTOCOL"].' 304 Not Modified');
			else header('Last-Modified: '.gmdate('D, d M Y H:i:s', $lastModified).' GMT', true, 304);
			header('Content-Length: '.$contentLength,true);
		break;
		case 400: //Bad Request
			header($_SERVER["SERVER_PROTOCOL"].' 400 Bad Request', true, 400);
			header('Content-Length: '.$contentLength,true); //is it needed?
		break;
		case 403:
			header('Origin not allowed', true, 403); //Not originated from allowed source
		break;
		case 404: //Not Found
			header($_SERVER["SERVER_PROTOCOL"].' 404 Not Found');
			header('Status: 404 Not Found');
		break;
		case 405:
			header('Allow: ' . $allowedMethods, true, 405); //Not allowed
		break;
		case 500:
			header('500 Internal Server Error', true, 500);
		break;
		case 501:
			header('Allow: ' . $implementedMethods, true, 501);
		break;
	}

}

function get_header () {

	if(!function_exists('getallheaders')) {
		foreach($_SERVER as $name => $value) {
			if(substr($name, 0, 5) == 'HTTP_') {
				$headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
			}
		}
	}
	else {
		$headers = getallheaders();
	}
	return $headers;
	
}

	// /**
	//  * Set header content type and cache control
	//  */
	// private function setHeader ($statusCode, $contentLength) {
		
	// 	//not strictly needed but may cause problems if omitted in some XHR request
	// 	if ($this->accessControlAllowOrigin == '*') header('Access-Control-Allow-Origin: *');
		
	// 	switch ($this->transport) {
	// 		case 'xml':
	// 		case 'json':
	// 		$_transport = 'application/'.$this->transport;
	// 		break;
	// 		case 'yaml':
	// 		$_transport = 'application/x-yaml';
	// 		break;
	// 		default:
	// 		$_transport = 'text/plain';
	// 		break;
	// 	}

	// 	switch ($statusCode) {
	// 		case 200: //OK
	// 			if ($this->ttl > 0) {
	// 				header('Content-type: '.$_transport);
	// 				header('Cache-Control: max-age='.$this->ttl.', must-revalidate');
	// 				header('Expires: '.gmdate("D, d M Y H:i:s", time() + $this->ttl)." GMT");
	// 			}
	// 			elseif ($this->ttl == 0) {
	// 				header('Content-type: '.$_transport,true);
	// 				header('Cache-Control: no-cache, must-revalidate');
	// 				header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
	// 			}
	// 			else {
	// 				header('Content-type: '.$_transport,true);
	// 			}
	// 			header('Content-Length: '.$contentLength,true);
	// 		break;
	// 		case 202: //Accepted
	// 			//PLEASE NOTE: according to HTTP/1.1, 202 header SHOULD HAVE status description in body... just in case
	// 			header($_SERVER["SERVER_PROTOCOL"].' 202 Accepted');
	// 			header('Status: 202 Accepted');
	// 			header('Content-Length: '.$contentLength,true);
	// 		break;
	// 		case 204: //OK - No Content
	// 			header($_SERVER["SERVER_PROTOCOL"].' 204 No Content');
	// 			header('Status: 204 No Content');
	// 			header('Content-Length: 0',true);
	// 		break;
	// 		case 201: //Created
	// 		case 301: //Moved Permanent
	// 		case 302: //Found
	// 		case 303: //See Other
	// 		case 307: //Temporary Redirect
	// 			header("Location: ".$this->statusCodeLocation,true,$statusCode);
	// 			header('Content-Length: '.$contentLength,true); //is it needed?
	// 		break;
	// 		case 304: //Not Modified
	// 			if (!$this->statusCode_resourceLastModified) header($_SERVER["SERVER_PROTOCOL"].' 304 Not Modified');
	// 			else header('Last-Modified: '.gmdate('D, d M Y H:i:s', $statusCode_resourceLastModified).' GMT', true, 304);
	// 			header('Content-Length: '.$contentLength,true);
	// 		break;
	// 		case 400: //Bad Request
	// 			header($_SERVER["SERVER_PROTOCOL"].' 400 Bad Request', true, 400);
	// 			header('Content-Length: '.$contentLength,true); //is it needed?
	// 		break;
	// 		case 403:
	// 			header('Origin not allowed', true, 403); //Not originated from allowed source
	// 		break;
	// 		case 404: //Not Found
	// 			header($_SERVER["SERVER_PROTOCOL"].' 404 Not Found');
	// 			header('Status: 404 Not Found');
	// 		break;
	// 		case 405:
	// 			header('Allow: ' . SUPPORTED_METHODS, true, 405); //Not allowed
	// 		break;
	// 		case 501:
	// 			header('Allow: ' . $this->serviceImplementedMethods, true, 501);
	// 		break;
	// 	}
		
	// }

?>