<?php

/** 
 * Main dispatcher configuration
 *
 * @package 	Comodojo dispatcher (Spare Parts)
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

define("DISPATCHER_REAL_PATH",realpath(dirname(__FILE__))."/../lib/comodojo");

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

######## BEGIN DISPATCHER FOLDERS ########

/**
* Cache folder.
* 
* @static	string
*/
define('DISPATCHER_CACHE_FOLDER', DISPATCHER_REAL_PATH."/../../cache/");

/**
* Services folder.
* 
* @static	string
*/
define('DISPATCHER_SERVICES_FOLDER', DISPATCHER_REAL_PATH."/../../services/");

/**
* Plugins folder.
* 
* @static	string
*/
define('DISPATCHER_PLUGINS_FOLDER', DISPATCHER_REAL_PATH."/../../plugins/");

######### END DISPATCHER FOLDERS #########

?>