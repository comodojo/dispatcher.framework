<?php namespace Comodojo\Dispatcher\Components;

use \Monolog\Handler\HandlerInterface;
use \Monolog\Logger;
use \Monolog\Handler\StreamHandler;
use \Monolog\Handler\SyslogHandler;
use \Monolog\Handler\ErrorLogHandler;
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


class LogManager {

    public $configuration;

    public $name;

    private static $levels = array(
        'INFO' => Logger::INFO,
        'NOTICE' => Logger::NOTICE,
        'WARNING' => Logger::WARNING,
        'ERROR' => Logger::ERROR,
        'CRITICAL' => Logger::CRITICAL,
        'ALERT' => Logger::ALERT,
        'EMERGENCY' => Logger::EMERGENCY,
        'DEBUG' => Logger::DEBUG
    );

    public function __construct(Configuration $configuration, $name = 'dispatcher') {

        $this->configuration = $configuration;
        $this->name = $name;

    }

    public function init() {

        $log = $this->configuration->get('log');

        $name = empty($log['name']) ? $this->name : $log['name'];

        $logger = new Logger($name);

        if (
            empty($log) ||
            ( isset($log['enabled']) && $log['enabled'] === false ) ||
            empty($log['providers'])
        ) {

            $logger->pushHandler( new NullHandler( self::getLevel() ) );

        } else {

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
    public static function create(Configuration $configuration, $name = 'dispatcher') {

        $log = new LogManager($configuration, $name);

        return $log->init();

    }

    protected function getHandler($provider, $parameters) {

        switch ( $parameters['type'] ) {

            case 'StreamHandler':
                $handler = $this->getStreamHandler($provider, $parameters);
                break;

            case 'SyslogHandler':
                $handler = $this->getSyslogHandler($provider, $parameters);
                break;

            case 'ErrorLogHandler':
                $handler = $this->getErrorLogHandler($provider, $parameters);
                break;

            default:
                $handler = null;
                break;

        }

        return $handler;

    }

    protected function getStreamHandler($name, $parameters) {

        $stream = $this->configuration->get('base-path').'/'.(empty($parameters['stream']) ? 'dispatcher.log' : $parameters['stream']);

        $level = self::getLevel( empty($parameters['level']) ? null : $parameters['level'] );

        $bubble = self::getBubble( empty($parameters['bubble']) ? true : $parameters['bubble'] );

        $filePermission = self::getFilePermission( empty($parameters['filePermission']) ? null : $parameters['filePermission'] );

        $useLocking = self::getLocking( empty($parameters['useLocking']) ? false : $parameters['useLocking'] );

        return new StreamHandler($stream, $level, $bubble, $filePermission, $useLocking);

    }

    protected function getSyslogHandler($name, $parameters) {

        if ( empty($parameters['ident']) ) return null;

        $facility = empty($parameters['facility']) ? LOG_USER : $parameters['facility'];

        $level = self::getLevel( empty($parameters['level']) ? null : $parameters['level'] );

        $bubble = self::getBubble( empty($parameters['bubble']) ? true : $parameters['bubble'] );

        $logopts = empty($parameters['logopts']) ? LOG_PID : $parameters['logopts'];

        return new SyslogHandler($parameters['ident'], $facility, $level, $bubble, $logopts);

    }

    protected function getErrorLogHandler($name, $parameters) {

        $messageType = empty($parameters['messageType']) ? ErrorLogHandler::OPERATING_SYSTEM : $parameters['messageType'];

        $level = self::getLevel( empty($parameters['level']) ? null : $parameters['level'] );

        $bubble = self::getBubble( empty($parameters['bubble']) ? true : $parameters['bubble'] );

        $expandNewlines = self::getExpandNewlines( empty($parameters['expandNewlines']) ? false : $parameters['expandNewlines'] );

        return new ErrorLogHandler($messageType, $level, $bubble, $expandNewlines);

    }

    /**
     * Map provided log level to level code
     *
     * @param   string    $level
     *
     * @return  integer
     */
    protected static function getLevel($level = null) {

        $level = strtoupper($level);

        if ( array_key_exists($level, self::$levels) ) return self::$levels[$level];

        return self::$levels['DEBUG'];

    }

    protected static function getBubble($bubble) {

        return filter_var($bubble, FILTER_VALIDATE_BOOLEAN, array(
            'options' => array(
                'default' => true
            )
        ));

    }

    protected static function getFilePermission($filepermission = null) {

        if ( is_null($filepermission) ) return null;

        return filter_var($filepermission, FILTER_VALIDATE_INT, array(
            'options' => array(
                'default' => 0644
            ),
            'flags' => FILTER_FLAG_ALLOW_OCTAL
        ));

    }

    protected static function getLocking($uselocking) {

        return filter_var($uselocking, FILTER_VALIDATE_BOOLEAN, array(
            'options' => array(
                'default' => false
            )
        ));

    }

    protected static function getExpandNewlines($expandNewlines) {

        return filter_var($expandNewlines, FILTER_VALIDATE_BOOLEAN, array(
            'options' => array(
                'default' => false
            )
        ));

    }

}
