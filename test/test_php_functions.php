<?php

/**
 * test_php_functions.php
 * 
 * Show some useful information about function required by
 * simpleDataRestDispatcher.
 * 
 * @package 	Comodojo Spare Parts
 * @author 		comodojo.org
 * @copyright 	__COPYRIGHT__ comodojo.org (info@comodojo.org)
 * @version 	__CURRENT_VERSION__
 * 
 * LICENSE:
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

function check_JSON() {
	return (function_exists("json_encode") AND function_exists("json_decode"));
}

function check_XML() {
	return (in_array('SimpleXML',get_loaded_extensions()));
}

function check_curl() {
	return (in_array('curl',get_loaded_extensions()));
}

function check_db_mysql() {
	return (function_exists("mysql_connect"));
}

function check_db_mysqli() {
	return (function_exists("mysqli_connect"));
}

function check_db_mysql_pdo() {
	return (in_array('pdo_mysql',get_loaded_extensions()));
}

function check_db_oracle_pdo() {
	return (in_array('pdo_oci',get_loaded_extensions()));
}

function check_db_sqlite_pdo() {
	return (in_array('pdo_sqlite',get_loaded_extensions()));
}

function check_db_db2() {
	return (function_exists("db2_pconnect"));
}

function check_db_dblib_pdo() {
	return (in_array('pdo_dblib',get_loaded_extensions()));
}

function check_db_postgresql() {
	return (function_exists("pg_connect"));
}

function check_folder_logs_permissions() {
	return is_readable(getcwd().'/../logs');
}

function check_folder_cache_permissions() {
	return is_readable(getcwd().'/../cache');
}

?>


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>Comodojo simpleDataRestDispatcher test scripts</title>
	<link rel="stylesheet" type="text/css" href="test.css" />
</head>

<body>

	<div id="testContainer">
	
		<div id="testHeader">
	
			<img src="../others/logo.png" alt="The Comodojo Logo"/>
	
		</div>
	
		<div id="testContent">
			
			<h1>Comodojo simpleDataRestDispatcher functions and extensions test</h1>
			<p>Follow test output on some important php components required (or optional) by simpleDataRestDispatcher.</p>
			
			<!--<div style="border: 1px solid #EEEEEE;">-->
			<?
			if (check_JSON()) echo '<div class="note">JSON native php extension loaded!</div>';
			else echo '<div class="important">JSON native php extension NOT loaded, dispatcher will use JSON.php library</div>';
			
			if (check_XML()) echo '<div class="note">SimpleXML php extension loaded!</div>';
			else echo '<div class="warning">No SimpleXML extension: services will be available only with JSON transport and XML requests will produce errors!</div>';
			
			if (check_curl()) echo '<div class="note">Curl php extension loaded!</div>';
			else echo '<div class="warning">No curl extension: url router will work only in \'ROUTE\' mode and \'CLOAK\' policy will produce errors. Also curl-based tests will fail.</div>';
			
			if (check_db_mysql()) echo '<div class="note">MySQL php extension loaded!</div>';
			else echo '<div class="important">No MySQL php extension installed: it will not be possible to connect to mysql database with native libs.</div>';
			
			if (check_db_mysqli()) echo '<div class="note">MySQLi php extension loaded!</div>';
			else echo '<div class="important">No MySQLi php extension installed: it will not be possible to connect to mysql database with improved libs.</div>';

			if (check_db_mysql_pdo()) echo '<div class="note">MySQL PDO extension loaded!</div>';
			else echo '<div class="important">No MySQL PDO installed: it will not be possible to connect to mysql database with PDO libs.</div>';
			
			if (check_db_oracle_pdo()) echo '<div class="note">Oracle (oci) PDO extension loaded!</div>';
			else echo '<div class="important">No Oracle PDO installed: it will not be possible to connect to oracle database with PDO libs.</div>';
			
			if (check_db_sqlite_pdo()) echo '<div class="note">SqLite PDO extension loaded!</div>';
			else echo '<div class="important">No SqLite PDO installed: it will not be possible to connect to sqlite database with PDO libs.</div>';
			
			if (check_db_db2()) echo '<div class="note">DB2 php extension loaded!</div>';
			else echo '<div class="important">No DB2 php extension installed: it will not be possible to connect to DB2 database with native libs.</div>';
			
			if (check_db_dblib_pdo()) echo '<div class="note">Dblib PDO extension loaded!</div>';
			else echo '<div class="important">No Dblib PDO installed: it will not be possible to connect to MsSQL or Sybase database with PDO libs.</div>';
			
			if (check_db_postgresql()) echo '<div class="note">PostgreSQL extension loaded!</div>';
			else echo '<div class="important">No PostgreSQL installed: it will not be possible to connect to PostgreSQL database with native libs.</div>';
			
			if (check_folder_logs_permissions()) echo '<div class="note">Logs folder writable!</div>';
			else echo '<div class="warning">Logs folder is not writable: services that use traces will procude errors!</div>';
			
			if (check_folder_logs_permissions()) echo '<div class="note">Cache folder writable!</div>';
			else echo '<div class="warning">Cache folder is not writable: router will not support caching and will produce errors on each cached request!</div>';
			?>
			<!--</div>-->
			
			<div style="border: 1px dotted #CCCCCC;">
				<h2>Some explanation</h2>
				<p>If you see symbols like <img src="../others/note.png" alt="note" /> or <img src="../others/important.png" alt="important" /> you can safely run simpleDataRestDispatcher, but some functions will not be available.</p>
				<p>Otherwise if you see symbols like <img src="../others/warning.png" alt="warning" /> you should consider to install related extension (required) prior to run simpleDataRestDispatcher, because it may produce errors.</p>
			</div>
		</div>
		
		<div id="testFooter">
		
			<p>&copy; 2011-2013 comodojo.org | <a href="http://www.comodojo.org" target="_blank">comodojo.org</a> | All Rights Reserved | Distributed under <a href="http://www.gnu.org/copyleft/gpl.html" target="_blank">GPL V3</a> terms</p>
		
		</div>
	
	</div>

</div>
</body>
</html>