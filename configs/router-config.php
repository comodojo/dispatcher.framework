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
 * @version	1.0
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
* Enable/disable the autoroute function. If true, services.php will try
* to route requests directed to services not declared explicit in routing
* table ($registered_services)
* 
* @static	bool
* @default	true
*/
define('AUTO_ROUTE', true);

/**
* Registered services (a.k.a. the routing table).
* Declare here services that router will understand. You can use also aliases
* to same service (for example with different service name).
* 
* @static	ARRAY
* @default	declared example services
*/
$registered_services = Array(
        'example_service'                   =>  Array("target"=>'example_service.php', "policy"=>'ROUTE'),
        'example_service_alias'             =>  Array("target"=>'example_service.php', "policy"=>'CLOAK'),
        'example_database_based_service'    =>  Array("target"=>'example_database_based_service.php', "policy"=>'CLOAK'),
        'example_external_service'          =>  Array("target"=>'example_external_service.php', "policy"=>'CLOAK'),
);

?>