<?php

/** 
 * 
 *
 * @package 	Comodojo Spare Parts
 * @author 		comodojo.org
 * @copyright 	__COPYRIGHT__ comodojo.org (info@comodojo.org)
 * @version 	__CURRENT_VERSION__
 */

######## BEGIN GLOBAL PROPERTIES ########

/**
 * Enable debug globally
 */
define('COMODOJO_GLOBAL_DEBUG_ENABLED', true);

/**
 * Debug log file (NULL will log to error_log)
 */
define('COMODOJO_GLOBAL_DEBUG_FILE', NULL);

/**
 * Debug level
 *
 * In order of relevance:
 * - DEBUG
 * - INFO
 * - WARNING
 * - ERROR
 */
define('COMODOJO_GLOBAL_DEBUG_LEVEL', 'DEBUG');

######### END GLOBAL PROPERTIES #########

######## BEGIN DISPATCHER PROPERTIES ########

/**
* If false, dispatcher will not route any request and will reply with an 503 Service 
* Temporarily Unavailable status code
* 
* @static	bool
* @default	true
*/
define ('DISPATCHER_ENABLED', true);

/**
* If true, dispatcher will use rewrite module to acquire service path and attibutes
* 
* @static	bool
* @default	true
*/
define ('DISPATCHER_USE_REWRITE', true);

/**
* Enable/disable the autoroute function. If true, dispatcher will try
* to route requests to not declared services using filenames
* 
* @static	bool
* @default	false
*/
define('DISPATCHER_AUTO_ROUTE', false);

/**
* Enable/disable cache support.
* 
* @static	bool
* @default	true
*/
define('DISPATCHER_CACHE_ENABLED', true);

/**
* Default cache time to live, in seconds.
* 
* @static	integer
* @default	600 (10 minutes)
*/
define('DISPATCHER_CACHE_DEFAULT_TTL', 600);

/**
* If true, cache will fail silently in case of error without throwing exception
* 
* @static	bool
* @default	true
*/
define('DISPATCHER_CACHE_FAIL_SILENTLY', true);

/**
* Cache folder.
* 
* @static	string
*/
define('DISPATCHER_CACHE_FOLDER', DISPATCHER_REAL_PATH."/../../cache/");

/**
 * Default encoding, currently used only in xml transformations
 */
define('DISPATCHER_DEFAULT_ENCODING', 'UTF-8');

/**
 * HTTP supported methods: GET, PUT, POST, DELETE or - as wildcard - ANY.
 *
 * You should not modify this value, because each service can implement one or
 * more HTTP methods independently. This value may change the Allow Response
 * Header in case of 405 response.
 * 
 * The one and only reason you may want to modify this value is to limit access
 * at your services to a subset of HTTP methods (i.e. if you want to disable PUT
 * requests globally, you can omit it from this definition; method will be 
 * ignored even though service implements it - or implements ANY wildcard).
 *
 * PLEASE NOTE: a service that not implements one of this methods, in case of
 * unsupported method request, will reply with a 501-not-implemented response;
 * this behaviour is managed automatically.
 *
 * WARNING: this constant should be in plain, uppercased, comma separated,
 * not spaced text.
 */
define('DISPATCHER_SUPPORTED_METHODS', 'GET,PUT,POST,DELETE,ANY');

######### END DISPATCHER PROPERTIES #########










// /**
//  * Enable tracing globally (this will override service specific config)
//  */
// define('GLOBAL_TRANSACTION_TRACING_ENABLED', false);

// /**
//  * Enable statistics/logging (not available for single service)
//  */
// define('GLOBAL_STATISTICS_ENABLED', true);







// COMODOJO_DEFAULT_DB_DATA_MODEL
// COMODOJO_DEFAULT_DB_HOST
// COMODOJO_DEFAULT_DB_PORT
// COMODOJO_DEFAULT_DB_NAME
// COMODOJO_DEFAULT_DB_USER
// COMODOJO_DEFAULT_DB_PASSWORD

// COMODOJO_CACHE_TTL

// COMODOJO_CACHE_FAIL_SILENTLY
// COMODOJO_CACHE_FOLDER
// COMODOJO_CACHE_ENABLED






// //***********************************************//

// /**
//  * Default service name (if null passed).
//  */
// define('DEFAULT_SERVICE_NAME', "undefined");

// /**
//  * Path for traces .log files
//  */
// define('TRANSACTION_TRACES_PATH', 'logs/');

// /**
//  * Default log file.
//  */
// define('DEFAULT_LOG_FILE', "global.log");

// //***********************************************//




// /**
//  * Statistics database host
//  */
// define('STATISTICS_DB_HOST', 'localhost');

// *
//  * Statistics database data model (database type)
 
// define('STATISTICS_DB_DATA_MODEL', 'MYSQLI');

// /**
//  * Statistics database port
//  */
// define('STATISTICS_DB_PORT', 3306);

// /**
//  * Statistics database name
//  */
// define('STATISTICS_DB_NAME', 'comodojo_services');

// /**
//  * Statistics database user
//  */
// define('STATISTICS_DB_USER', 'comodojo');

// /**
//  * Statistics database password
//  */
// define('STATISTICS_DB_PASSWORD', 'password');

// /**
//  * DEFAULT database host
//  */
// define('DEFAULT_DB_HOST', 'localhost');

// /**
//  * DEFAULT database port
//  */
// define('DEFAULT_DB_PORT', 3306);

// /**
//  * DEFAULT database data model (database type)
//  */
// define('DEFAULT_DB_DATA_MODEL', 'MYSQLI');

// /**
//  * DEFAULT database name
//  */
// define('DEFAULT_DB_NAME', 'comodojo_services');

// /**
//  * DEFAULT database user
//  */
// define('DEFAULT_DB_USER', 'comodojo');

// /**
//  * DEFAULT database password
//  */
// define('DEFAULT_DB_PASSWORD', 'password');

// /**
//  * DEFAULT database fetch method
//  */
// define('DEFAULT_DB_FETCH', 'ASSOC');

?>