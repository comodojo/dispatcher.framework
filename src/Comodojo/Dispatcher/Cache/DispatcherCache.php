<?php namespace Comodojo\Dispatcher\Cache;

use \Psr\Log\LoggerInterface;
use \Comodojo\Cache\CacheInterface\CacheInterface;
use \Comodojo\Cache\CacheManager;
use \Comodojo\Cache\FileCache;
use \comodojo\Dispatcher\Components\Configuration;

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

class DispatcherCache {

    private $configuration;

    private $logger;

    public function __construct(Configuration $configuration, LoggerInterface $logger) {

        $this->configuration = $configuration;

        $this->logger = $logger;

    }

    public function init() {

        $cache = $this->configuration->get('cache');

        if ( empty($cache) ) {

            $manager = new CacheManager(self::getAlgorithm(), $this->logger);

        } else {

            $enabled = ( empty($cache['enabled']) || $cache['enabled'] === true ) ? true : false;

            $algorithm = self::getAlgorithm( empty($cache['algorithm']) ? null : $cache['algorithm']);

            $manager = new CacheManager($algorithm, $this->logger);

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

        $cache = new DispatcherCache($configuration, $logger);

        return $cache->init();

    }

    protected function getHandler($provider, $parameters) {

        switch ( strtolower($parameters['type']) ) {

            case 'filecache':

                $folder = empty($parameters['folder']) ? '' : $parameters['$folder'];

                $target = $this->configuration->get('base-path').'/'.$folder;

                $handler = new FileCache($target);

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
                $selected = CacheManager::PICK_LAST;
                break;

            case 'PICK_RANDOM':
                $selected = CacheManager::PICK_RANDOM;
                break;

            case 'PICK_BYWEIGHT':
                $selected = CacheManager::PICK_BYWEIGHT;
                break;

            case 'PICK_ALL':
                $selected = CacheManager::PICK_ALL;
                break;

            case 'PICK_FIRST':
            default:
                $selected = CacheManager::PICK_FIRST;
                break;

        }

        return $selected;

    }

}
