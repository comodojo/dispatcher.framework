<?php namespace comodojo;

/** 
 * 
 *
 * @package 	Comodojo Spare Parts
 * @author 		comodojo.org
 * @copyright 	__COPYRIGHT__ comodojo.org (info@comodojo.org)
 * @version 	__CURRENT_VERSION__
 */


COMODOJO_DEFAULT_DB_DATA_MODEL
COMODOJO_DEFAULT_DB_HOST
COMODOJO_DEFAULT_DB_PORT
COMODOJO_DEFAULT_DB_NAME
COMODOJO_DEFAULT_DB_USER
COMODOJO_DEFAULT_DB_PASSWORD

COMODOJO_CACHE_TTL

COMODOJO_CACHE_FAIL_SILENTLY
COMODOJO_CACHE_FOLDER
COMODOJO_CACHE_ENABLED



define ('DISPATCHER_USE_REWRITE', true);

/**
* Enable/disable the autoroute function. If true, index.php will try
* to route requests directed to services not declared explicit in routing
* table ($registered_services)
* 
* @static	bool
* @default	true
*/
define('DISPATCHER_AUTO_ROUTE', true);

/**
* Default cache time to live, in seconds.
* 
* @static	INT
* @default	600 (10 minutes)
*/
define('DISPATCHER_CACHE_DEFAULT_TTL', 600);

/**
 * Default encoding, currently used only in xml transformations.
 *
 * Should be the same of simpleDataRestDispatcher
 */
define('DISPATCHER_DEFAULT_ENCODING', 'UTF-8');







/**
 * Enable debug globally (this will override service specific config)
 */
define('GLOBAL_DEBUG_ENABLED', false);

/**
 * Enable tracing globally (this will override service specific config)
 */
define('GLOBAL_TRANSACTION_TRACING_ENABLED', false);

/**
 * Enable statistics/logging (not available for single service)
 */
define('GLOBAL_STATISTICS_ENABLED', true);

//***********************************************//

/**
 * Default service name (if null passed).
 */
define('DEFAULT_SERVICE_NAME', "undefined");

/**
 * Path for traces .log files
 */
define('TRANSACTION_TRACES_PATH', 'logs/');

/**
 * Default log file.
 */
define('DEFAULT_LOG_FILE', "global.log");

//***********************************************//

/**
 * Supported methods (GET, PUT, POST, or DELETE).
 *
 * You should not modify this value, because each service can implement one or
 * more HTTP methods indipendently. This value only changes the Allow Response
 * Header in case of 405 response
 * 
 * The one and only reason you may have to modify this value is to limit access
 * at your services to a subset of HTTP methods (i.e. if you don't want PUT
 * requests, you can omit it from definition; method will be ignored even though
 * service implements it - or implements global logic...).
 *
 * PLEASE NOTE: a service that not implements one of those methods, in case of
 * unsupported method request, will reply with a 501-not-implemented response;
 * this behaviour is managed automatically.
 *
 * WARNING: this constant should be in plain, uppercased, comma separated,
 * not spaced text to work as designed.
 */
define('DISPATCHER_SUPPORTED_METHODS', 'GET,PUT,POST,DELETE,ANY');


/**
 * Statistics database host
 */
define('STATISTICS_DB_HOST', 'localhost');

/**
 * Statistics database data model (database type)
 */
define('STATISTICS_DB_DATA_MODEL', 'MYSQLI');

/**
 * Statistics database port
 */
define('STATISTICS_DB_PORT', 3306);

/**
 * Statistics database name
 */
define('STATISTICS_DB_NAME', 'comodojo_services');

/**
 * Statistics database user
 */
define('STATISTICS_DB_USER', 'comodojo');

/**
 * Statistics database password
 */
define('STATISTICS_DB_PASSWORD', 'password');

/**
 * DEFAULT database host
 */
define('DEFAULT_DB_HOST', 'localhost');

/**
 * DEFAULT database port
 */
define('DEFAULT_DB_PORT', 3306);

/**
 * DEFAULT database data model (database type)
 */
define('DEFAULT_DB_DATA_MODEL', 'MYSQLI');

/**
 * DEFAULT database name
 */
define('DEFAULT_DB_NAME', 'comodojo_services');

/**
 * DEFAULT database user
 */
define('DEFAULT_DB_USER', 'comodojo');

/**
 * DEFAULT database password
 */
define('DEFAULT_DB_PASSWORD', 'password');

/**
 * DEFAULT database fetch method
 */
define('DEFAULT_DB_FETCH', 'ASSOC');

?>