<?php namespace Comodojo\Dispatcher\Log;

use \Monolog\Logger;
use \Monolog\Handler\StreamHandler;
use \Monolog\Handler\NullHandler;

/**
 *
 *
 * @package     Comodojo Framework
 * @author      Marco Giovinazzi <marco.giovinazzi@comodojo.org>
 * @author      Marco Castiello <marco.castiello@gmail.com>
 * @license     GPL-3.0+
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

class DispatcherLogger {
    
    /**
     * Create the logger
     *
     * @param bool $verbose
     *
     * @return \Monolog\Logger
     */
    public static function create(Configuration $configuration) {
    
        $name = $configuration->get('dispatcher-log-name');
        
        $enabled = $configuration->get('dispatcher-log-enabled');
        
        $level = self::getLevel( $configuration->get('dispatcher-log-level') );
        
        $target = $configuration->get('dispatcher-log-target');
    
        $logger = new Logger($name);
        
        if ( $enabled === true ) {
            
            $logger->pushHandler( new StreamHandler( $target, $level) );
            
        } else {
            
            $logger->pushHandler( new NullHandler($level) );
            
        }
        
        return $logger;
        
    }
    
    /**
     * Map provided log level to level code
     *
     * @param   string    $level
     *
     * @return  integer
     */
    protected static function getLevel($level) {

        switch ( strtoupper($level) ) {

            case 'INFO':
                $logger_level = Logger::INFO;
                break;

            case 'NOTICE':
                $logger_level = Logger::NOTICE;
                break;

            case 'WARNING':
                $logger_level = Logger::WARNING;
                break;

            case 'ERROR':
                $logger_level = Logger::ERROR;
                break;

            case 'CRITICAL':
                $logger_level = Logger::CRITICAL;
                break;

            case 'ALERT':
                $logger_level = Logger::ALERT;
                break;

            case 'EMERGENCY':
                $logger_level = Logger::EMERGENCY;
                break;

            case 'DEBUG':
            default:
                $logger_level = Logger::DEBUG;
                break;

        }

        return $logger_level;

    }
    
}