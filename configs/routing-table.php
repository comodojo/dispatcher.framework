<?php

/** 
 * 
 *
 * @package 	Comodojo Spare Parts
 * @author 		comodojo.org
 * @copyright 	__COPYRIGHT__ comodojo.org (info@comodojo.org)
 * @version 	__CURRENT_VERSION__
 */

/**
* Registered services (a.k.a. the routing table).
* Declare here services that router will understand. You can use also aliases
* to same service (for example with different service name).
*
* AT LEAST TWO VALUES ARE REQUIRED FOR EACH PATH:
* - target: target service script
* - policy: routing policy (route/cloak)
*
* If they are not specified, router will route request according to AUTO_ROUTE
* policies.
*
* OTHER OPTIONAL VALUES:
* - cache:                      SERVER, CLIENT, BOTH; If false, do not use cache
* - ttl:                        cache time to live
* - accessControlAllowOrigin:   FALSE, '*', _URL_
* - customHeaders:              Array of header values (if defined) to throw
*                               back to client
* - forceMethod:                GET,POST,PUT,DELETE; force curl request to be in
*                               specified method overriding original request one
* - redirectStatusCode          Force 3xx status code in a routed request. If
*                               none specified, router will redirect request
*                               using 302 status code (Found).
*
* PLEASE NOTE: router cache will work ONLY if following conditions (+) are met:
* + HTTP METHOD is [GET]
* + routing policy is to [CLOAK] response
* + response ends with 200 status code
*
* WARNING: caching will speedup your service, but you will loose statistics,
* traces and debug info!
*
* PLEASE NOTE ALSO: when routing POST, PUT or DELETE requests, if no
* redirectStatusCode is defined, router will send a 302 redirect status code to
* the browser. In that situation most modern browsers will convert original
* request in GET, repackaging request attributes. In other hand, forcing a 300
* redirect status code (Multiple Choises), could generate sometimes a browser
* error or require a user action (confirmation dialog).
* 
* 
* @static	ARRAY
* @default	declared example services
*/

//global $dispatcher;

//$dispatcher->setRoute("serve","ROUTE","serve.php");

//$dispatcher->setRoute("default","ROUTE","serve.php");

//$dispatcher->addHook("dispatcher.serviceroute", "comodojo\debug");



?>