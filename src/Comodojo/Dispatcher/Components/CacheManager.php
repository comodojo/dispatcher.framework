<?php namespace Comodojo\Dispatcher\Components;

use \Comodojo\Dispatcher\Components\Model as DispatcherClassModel;
use \comodojo\Dispatcher\Components\Configuration;
use \Comodojo\Cache\Providers\ProviderInterface;
use \Comodojo\Cache\Providers\Filesystem;
use \Comodojo\Cache\Providers\Memcached;
use \Comodojo\Cache\Providers\Apc;
use \Comodojo\Cache\Providers\PhpRedis;
use \Comodojo\Cache\Cache;
use \Psr\Log\LoggerInterface;

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

class CacheManager extends DispatcherClassModel {

    protected static $algorithms = array(
        'PICK_LAST' => Cache::PICK_LAST,
        'PICK_RANDOM' => Cache::PICK_RANDOM,
        'PICK_BYWEIGHT' => Cache::PICK_BYWEIGHT,
        'PICK_ALL' => Cache::PICK_ALL,
        'PICK_TRAVERSE' => Cache::PICK_TRAVERSE,
        'PICK_FIRST' => Cache::PICK_FIRST
    );

    public function init() {

        $cache = $this->configuration->get('cache');

        $algorithm = self::getAlgorithm( empty($cache['algorithm']) ? null : $cache['algorithm']);

        $manager = new Cache($algorithm, $this->logger);

        if ( !empty($cache) && !empty($cache['providers']) && is_array($cache['providers']) ) {

            foreach ($cache['providers'] as $provider => $parameters) {

                list($handler, $weight) = $this->getHandler($provider, $parameters);

                if ( $handler instanceof ProviderInterface ) $manager->addProvider($handler, $weight);

            }

        }

        return $manager;

    }

    /**
     * Create the Cache Manager
     *
     * @return \Comodojo\Cache\Cache
     */
    public static function create(Configuration $configuration, LoggerInterface $logger) {

        $cache = new CacheManager($configuration, $logger);

        return $cache->init();

    }

    protected function getHandler($provider, $parameters) {

        if ( empty($parameters['type']) ) return array(null, null);

        $weight = empty($parameters['weight']) ? 0 : $parameters['weight'];

        switch ( $parameters['type'] ) {

            case 'Filesystem':

                $handler = $this->getFilesystemProvider($provider, $parameters);

                break;

            case 'Apc':

                $handler = $this->getApcProvider($provider, $parameters);

                break;

            case 'Memcached':

                $handler = $this->getMemcachedProvider($provider, $parameters);

                break;

            case 'PhpRedis':

                $handler = $this->getPhpRedisProvider($provider, $parameters);

                break;

            default:
                $handler = null;
                break;
        }

        return array($handler, $weight);

    }

    protected function getFilesystemProvider($provider, $parameters) {

        $base = $this->configuration->get('base-path');

        if ( empty($parameters['folder']) ||  empty($base) ) {
            $this->logger->warning("Wrong cache provider, disabling $provider", $parameters);
            return null;
        }

        $target = $base.'/'.$parameters['folder'];

        $handler = new Filesystem($target);

    }

    protected function getApcProvider($provider, $parameters) {

        return new Apc();

    }

    protected function getMemcachedProvider($provider, $parameters) {

        $server = empty($parameters['server']) ? '127.0.0.1' : $parameters['server'];
        $port = empty($parameters['port']) ? '1121' : $parameters['port'];
        $weight = empty($parameters['weight']) ? 0 : $parameters['weight'];
        $persistent_id = empty($parameters['persistent_id']) ? null : $parameters['persistent_id'];

        return new Memcached($server, $port, $weight, $persistent_id);

    }

    protected function getPhpRedisProvider($provider, $parameters) {

        $server = empty($parameters['server']) ? '127.0.0.1' : $parameters['server'];
        $port = empty($parameters['port']) ? '1121' : $parameters['port'];
        $timeout = empty($parameters['timeout']) ? 0 : $parameters['timeout'];

        return new PhpRedis($server, $port, $timeout);

    }

    /**
     * Map provided log level to level code
     *
     * @param   string    $algorithm
     *
     * @return  integer
     */
    protected static function getAlgorithm($algorithm = null) {

        $algorithm = strtoupper($algorithm);

        if ( array_key_exists($algorithm, self::$algorithms) ) return self::$algorithms[$algorithm];

        return self::$algorithms['PICK_FIRST'];

    }

}
