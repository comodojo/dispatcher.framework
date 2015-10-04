<?php namespace Comodojo\Dispatcher;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\NullHandler;

/**
 * Init the monolog logger/debugger
 * 
 * @package     Comodojo dispatcher
 * @author      Marco Giovinazzi <marco.giovinazzi@comodojo.org>
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

class Debug {

    private $logger = null;

    final public function __construct() {

        $enabled = defined('DISPATCHER_LOG_ENABLED') ? filter_var(DISPATCHER_LOG_ENABLED, FILTER_VALIDATE_BOOLEAN) : false;

        $name = defined('DISPATCHER_LOG_NAME') ? DISPATCHER_LOG_NAME : 'dispatcher-default';

        $level = $this->getLevel( defined('DISPATCHER_LOG_LEVEL') ? DISPATCHER_LOG_LEVEL : 'DEBUG' );

        $target = defined('DISPATCHER_LOG_TARGET') ? DISPATCHER_LOG_TARGET : null;

        $this->logger = new Logger($name);

        if ( $enabled ) {

            $handler = is_null($target) ? new ErrorLogHandler() : new StreamHandler( defined('DISPATCHER_LOG_FOLDER') ? DISPATCHER_LOG_FOLDER.$target : $target, $level);

        }
        else {

            $handler = new NullHandler($level);

        }

        $this->logger->pushHandler($handler);

    }

    final public function info($message, array $context = array()) {

        $log = $this->logger->addInfo($message, $context);

        return $log;

    }

    final public function notice($message, array $context = array()) {

        $log = $this->logger->addNotice($message, $context);

        return $log;

    }

    final public function warning($message, array $context = array()) {

        $log = $this->logger->addWarning($message, $context);

        return $log;

    }

    final public function error($message, array $context = array()) {

        $log = $this->logger->addError($message, $context);

        return $log;

    }

    final public function critical($message, array $context = array()) {

        $log = $this->logger->addCritical($message, $context);

        return $log;

    }

    final public function alert($message, array $context = array()) {

        $log = $this->logger->addAlert($message, $context);

        return $log;

    }

    final public function emergency($message, array $context = array()) {

        $log = $this->logger->addEmergency($message, $context);

        return $log;

    }

    final public function debug($message, array $context = array()) {

        $log = $this->logger->addDebug($message, $context);

        return $log;

    }


    private function getLevel($level) {

        switch (strtoupper($level)) {

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