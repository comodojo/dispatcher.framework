<?php namespace Comodojo\Dispatcher\Log;

use \Monolog\Handler\HandlerInterface;
use \Monolog\Logger;
use \Monolog\Handler\StreamHandler;
use \Monolog\Handler\NullHandler;
use \Comodojo\Dispatcher\Components\Configuration;

/**
 * @package     Comodojo Dispatcher
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

    private $configuration;

    public function __construct(Configuration $configuration) {

        $this->configuration = $configuration;

    }

    public function init() {

        $log = $this->configuration->get('log');

        if (
            empty($log) ||
            ( isset($log['enabled']) && $log['enabled'] === false ) ||
            empty($log['providers'])
        ) {

            $logger = new Logger('dispatcher');

            $logger->pushHandler( new NullHandler( self::getLevel() ) );

        } else {

            $name = empty($log['name']) ? 'dispatcher' : $log['name'];

            $logger = new Logger($name);

            foreach ($log['providers'] as $provider => $parameters) {

                $handler = $this->getHandler($provider, $parameters);

                if ( $handler instanceof HandlerInterface ) $logger->pushHandler($handler);

            }

        }

        return $logger;

    }

    /**
     * Create the logger
     *
     * @param Configuration $configuration
     *
     * @return Logger
     */
    public static function create(Configuration $configuration) {

        $log = new DispatcherLogger($configuration);

        return $log->init();

    }

    protected function getHandler($provider, $parameters) {

        switch ( strtolower($parameters['type']) ) {

            case 'streamhandler':

                $target = empty($parameters['target']) ? 'dispatcher.log' : $parameters['target'];

                $file = $this->configuration->get('base-path').'/'.$target;

                $level = self::getLevel( empty($parameters['level']) ? 'info' : $parameters['level'] );

                $handler = new StreamHandler($file, $level);

                break;

            default:
                $handler = null;
                break;
        }

        return $handler;

    }

    /**
     * Map provided log level to level code
     *
     * @param   string    $level
     *
     * @return  integer
     */
    protected static function getLevel($level = null) {

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
