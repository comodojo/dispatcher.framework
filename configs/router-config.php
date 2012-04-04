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
 * @version	{*_CURRENT_VERSION_*}
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
 * Serialize in query string request parameters.
 *
 * Most browser, if redirected, tend to loose parameters and switch to a GET
 * request (no matter if the initial one was a POST/PUT/...).
 *
 * With this switch enabled, router will try to serialize parameters and recompose
 * the query string to serve.
 *
 * In a GET, nothing will be loosed (except the "service" tag).
 *
 * In other requests, this require that your service shoult support also GET
 * method (or it uses th "logic" constructor).
 * 
 * @static	BOOL
 * @default     true
 */
define('SERIALIZE_PARAMETERS_IN_ROUTING', true);

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
* - FALSE: disable autocache (Expires 1970 will be sent to clients).
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
 * Default access control origin, according to W3C Access Control specification.
 *
 * For more information, please visit following link.
 *
 * @link http://dev.w3.org/2006/waf/access-control/
 *
 * Possible values:
 * - false  will disable completely access-control header (useful for manual
 *          access control management or just framework hacking)
 * - '*'    will send an 'Access-Control-Allow-Origin: *', just in case :)
 * - _URL_  will restrict access with 'Access-Control-Allow-Origin: _URL_'
 *
 * PLEASE NOTE: access-control is a bit tricky. You can use it in single
 * services OR in router BUT NOT in both of them! Please see "A NOTE ON
 * ACCESS CONTROL" section in README file.
 *
 * WARNING: this setting, unless explicitly defined in routing table, will
 * influence the behaviour of the whole router.
 * 
 */
define('DEFAULT_ACCESS_CONTROL_ALLOW_ORIGIN', false);

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
$registered_services = Array(
    
    'example_hello_world'                           =>  Array("target"=>'example_hello_world.php', "policy"=>'ROUTE'),
    'example_hello_world_alias_301'                 =>  Array("target"=>'example_hello_world.php', "policy"=>'ROUTE', "redirectStatusCode"=>301),
    'example_hello_world_alias'                     =>  Array("target"=>'example_hello_world.php', "policy"=>'CLOAK'),
    'example_hello_world_alias_cached'              =>  Array("target"=>'example_hello_world.php', "policy"=>'CLOAK', "cache"=>'BOTH', "ttl"=>600),
    'example_hello_world_alias_origin'              =>  Array("target"=>'example_hello_world.php', "policy"=>'CLOAK', "accessControlAllowOrigin"=>"comodojo.org"),
    'example_hello_world_alias_forcepost'           =>  Array("target"=>'example_hello_world.php', "policy"=>'CLOAK', "forceMethod"=>"POST"),

    'example_database_based_service'                =>  Array("target"=>'example_database_based_service.php', "policy"=>'ROUTE'),
    'example_database_based_service_alias'          =>  Array("target"=>'example_database_based_service.php', "policy"=>'CLOAK'),
    'example_database_based_service_alias_cached'   =>  Array("target"=>'example_database_based_service.php', "policy"=>'CLOAK', "cache"=>'SERVER', "ttl"=>30),
    
    'example_external_service'                      =>  Array("target"=>'example_external_service.php', "policy"=>'CLOAK'),
    'example_external_service_cached_srv'           =>  Array("target"=>'example_external_service.php', "policy"=>'CLOAK', "cache"=>'SERVER', "ttl"=>600),
    'example_external_service_cached_cli'           =>  Array("target"=>'example_external_service.php', "policy"=>'CLOAK', "cache"=>'CLIENT', "ttl"=>600)
);

?>