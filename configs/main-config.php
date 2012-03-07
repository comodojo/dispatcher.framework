<?php

/** 
 * Base configurations of simpleDataRestDispatcher.
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
 * Path for traces .log files
 */
define('TRANSACTION_TRACES_PATH', 'logs/');

//***********************************************//

/**
 * Default encoding, currently used only in xml transformations
 */
define('DEFAULT_ENCODING', 'UTF-8');

/**
 * Default transport, used if not specified in query string
 */
define('DEFAULT_TRANSPORT', 'JSON');

/**
 * Default cache ttl.
 *
 * INT represent seconds, 0 will disable cache, -1 will prevent to send header
 */
define('DEFAULT_TTL', -1);

/**
 * Default log file.
 *
 */
define('DEFAULT_LOG_FILE', "global.log");

/**
 * Default log file.
 *
 */
define('DEFAULT_SERVICE_NAME', "undefined");

//***********************************************//

/**
 * Statistics database host
 */
define('STATISTICS_DB_HOST', 'localhost');

/**
 * Statistics database data model (database type)
 */
define('STATISTICS_DB_DATA_MODEL', 'MYSQL');

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

//***********************************************//

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
define('DEFAULT_DB_DATA_MODEL', 'MYSQL');

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

//***********************************************//

?>