<?php

/**
 * router-config.php
 * 
 * Base configurations of "index.php" - simple URL router
 * for simpleDataRestDispatcher.
 *
 * This file is intended to be a starting point. You can edit it according
 * to your needs.
 *
 * @package	Comodojo Spare Parts
 * @author	comodojo.org
 * @copyright	2012 comodojo.org (info@comodojo.org)
 * @version	1.1
 * 
 */

//***********************************************//

/**
 * Set the default behaviour (policy) for the router.
 *
 * If defined as 'ROUTE', index.php will act as a router redirecting
 * requests to single service.
 *
 * If defined as 'CLOAK', index.php will masquerade requested service
 * acting as a service itself.
 *
 * WARNING: cloak policy require CURL installed and available on your
 * php installation!
 *
 * @static	string
 * @default     'ROUTE'
 */
define('DEFAULT_POLICY', 'ROUTE');

/**
* Default metadata transport (should be the same as simpleDataRestDispatcher)
* @static	string
* @default	"JSON"
*/
define('DEFAULT_TRANSPORT', 'JSON');

/**
* Enable/disable the autoroute function. If true, index.php will try
* to route requests directed to services not declared explicit in routing
* table ($registered_services)
* 
* @static	bool
* @default	true
*/
define('AUTO_ROUTE', true);

/**
* Enable/disable the autocache function. Possible values:
* 
* - SERVER: means that server will cache request in local file, but "no-cache"
*   cache control header + Expires 1970 will be sent to clients.
*   
* - CLIENT: means that server will not cache request but will send a max-age +
*   Expires headers as specified in TTL field
*
* - BOTH: means that both server and client will receive a cache directive;
*   server will cache request in a local file, client will receive a max-age +
*   Expires header equal to local cache expire time as
*                       best-before = [ (local+ttl) - now ]
*
* - FALSE: disable autocache.
*
* PLEASE NOTE: CACHE DON'T WORK WITH ROUTE POLICY
* 
* @static	bool
* @default	false
*/
define('AUTO_CACHE', false);

/**
* Default cache time to live, in seconds.
* 
* @static	INT
* @default	600 (10 minutes)
*/
define('DEFAULT_TTL', 600);

/**
 * Default encoding, currently used only in xml transformations.
 *
 * Should be the same of simpleDataRestDispatcher
 */
define('DEFAULT_ENCODING', 'UTF-8');

/**
* Registered services (a.k.a. the routing table).
* Declare here services that router will understand. You can use also aliases
* to same service (for example with different service name).
*
* PLEASE NOTE: AT LEAST TWO VALUES ARE REQUIRED:
* - target: target service script
* - policy: routing policy (route/cloak)
*
* If they are not specified, router will route request according to AUTO_ROUTE
* policies.
*
* OTHER VALUES:
* - cache:      SERVER, CLIENT, BOTH. If false, don't use cache.
* - ttl:        cache time to live
*
* PLEASE NOTE: CACHE DON'T WORK WITH ROUTE POLICY
*
* WARNING: caching will speedup your service, but you will loose statistics, traces and debug info!
* 
* @static	ARRAY
* @default	declared example services
*/
$registered_services = Array(
    
    'example_service'                               =>  Array("target"=>'example_service.php', "policy"=>'ROUTE'),
    'example_service_alias'                         =>  Array("target"=>'example_service.php', "policy"=>'CLOAK'),
    'example_service_alias_cached'                  =>  Array("target"=>'example_service.php', "policy"=>'CLOAK', "cache"=>'BOTH', "ttl"=>600),
    
    'example_database_based_service'                =>  Array("target"=>'example_database_based_service.php', "policy"=>'ROUTE'),
    'example_database_based_service_alias'          =>  Array("target"=>'example_database_based_service.php', "policy"=>'CLOAK'),
    'example_database_based_service_alias_cached'   =>  Array("target"=>'example_database_based_service.php', "policy"=>'CLOAK', "cache"=>'SERVER', "ttl"=>30),
    
    'example_external_service'                      =>  Array("target"=>'example_external_service.php', "policy"=>'CLOAK'),
    'example_external_service_cached'               =>  Array("target"=>'example_external_service.php', "policy"=>'CLOAK', "cache"=>'CLIENT', "ttl"=>600)
);

?>