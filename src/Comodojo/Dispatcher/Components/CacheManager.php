<?php namespace Comodojo\Dispatcher\Components;

use \Comodojo\Dispatcher\Components\Model as DispatcherClassModel;
use \comodojo\Dispatcher\Components\Configuration;
use \Comodojo\Cache\CacheInterface\CacheInterface;
use \Comodojo\Cache\CacheManager as ComodojoCacheManager;
use \Comodojo\Cache\FileCache;
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

class CacheManager extends DispatcherClassModel{

    public function init() {

        $cache = $this->configuration()->get('cache');

        if ( empty($cache) ) {

            $manager = new ComodojoCacheManager(self::getAlgorithm(), $this->logger);

        } else {

            $enabled = ( empty($cache['enabled']) || $cache['enabled'] === true ) ? true : false;

            $algorithm = self::getAlgorithm( empty($cache['algorithm']) ? null : $cache['algorithm']);

            $manager = new ComodojoCacheManager($algorithm, $this->logger);

            if ( $enabled && !empty($cache['providers']) ) {

                foreach ($cache['providers'] as $provider => $parameters) {

                    $handler = $this->getHandler($provider, $parameters);

                    if ( $handler instanceof CacheInterface ) $manager->addProvider($handler);

                }

            }

        }

        return $manager;

    }

    /**
     * Create the Cache Manager
     *
     * @return \Comodojo\Cache\CacheManager
     */
    public static function create(Configuration $configuration, LoggerInterface $logger) {

        $cache = new CacheManager($configuration, $logger);

        return $cache->init();

    }

    protected function getHandler($provider, $parameters) {

        switch ( strtolower($parameters['type']) ) {

            case 'filecache':

                $base = $this->configuration->get('base-path');

                if ( empty($parameters['folder']) ||  empty($base) ) {
                    $this->logger->warning("Wrong cache provider, disabling $provider", $parameters);
                    break;
                }

                $target = $base.'/'.$parameters['folder'];

                $handler = new FileCache($target);

                break;

            case 'memcached':

                if ( empty($parameters['host']) ) {
                    $this->logger->warning("Wrong cache provider, disabling $provider", $parameters);
                    break;
                }

                $port = empty($parameters['port']) ? 11211 : intval($parameters['port']);

                $weight = empty($parameters['weight']) ? 0 : intval($parameters['weight']);

                $persistentid = empty($parameters['persistent-id']) ? null : boolval($parameters['persistentid']);

                $handler = new MemcachedCache($host, $port, $weight, $persistentid);

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
     * @param   string    $algorithm
     *
     * @return  integer
     */
    protected static function getAlgorithm($algorithm = null) {

        switch ( strtoupper($algorithm) ) {

            case 'PICK_LAST':
                $selected = ComodojoCacheManager::PICK_LAST;
                break;

            case 'PICK_RANDOM':
                $selected = ComodojoCacheManager::PICK_RANDOM;
                break;

            case 'PICK_BYWEIGHT':
                $selected = ComodojoCacheManager::PICK_BYWEIGHT;
                break;

            case 'PICK_ALL':
                $selected = ComodojoCacheManager::PICK_ALL;
                break;

            case 'PICK_FIRST':
            default:
                $selected = ComodojoCacheManager::PICK_FIRST;
                break;

        }

        return $selected;

    }

}
