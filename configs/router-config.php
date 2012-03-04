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
* Enable/disable the autocache function. If true, index.php will cache each
* cloaked request, unless explicitely declared in routing table
* ($registered_services)
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
* Registered services (a.k.a. the routing table).
* Declare here services that router will understand. You can use also aliases
* to same service (for example with different service name).
*
* PLEASE NOTE: AT LEAST TWO VALUES ARE REQUIRED:
* - target: target service script
* - policy: routing policy (route/cloak)
*
* OTHER VALUES:
* - cache:  if true, router will cache request
* - ttl:    cache time to live
*
* WARNING: caching will speedup your service, but you will loose statistics, traces and debug info!
* 
* @static	ARRAY
* @default	declared example services
*/
$registered_services = Array(
    
    'example_service'                               =>  Array("target"=>'example_service.php', "policy"=>'ROUTE'),
    'example_service_alias'                         =>  Array("target"=>'example_service.php', "policy"=>'CLOAK'),
    'example_service_alias_cached'                  =>  Array("target"=>'example_service.php', "policy"=>'CLOAK', "cache"=>true, "ttl"=>600),
    
    'example_database_based_service'                =>  Array("target"=>'example_database_based_service.php', "policy"=>'ROUTE'),
    'example_database_based_service_alias'          =>  Array("target"=>'example_database_based_service.php', "policy"=>'CLOAK'),
    'example_database_based_service_alias_cached'   =>  Array("target"=>'example_database_based_service.php', "policy"=>'CLOAK', "cache"=>true, "ttl"=>30),
    
    'example_external_service'                      =>  Array("target"=>'example_external_service.php', "policy"=>'CLOAK'),
    'example_external_service_cached'               =>  Array("target"=>'example_external_service.php', "policy"=>'CLOAK', "cache"=>true, "ttl"=>600)
);

?>