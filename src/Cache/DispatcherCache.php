<?php namespace Comodojo\Dispatcher\Cache;

use \Monolog\Logger;
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

    /**
     * Create the Cache Manager
     *
     * @return \Comodojo\Cache\CacheManager
     */
    public static function create(Configuration $configuration, Logger $logger) {

        $enabled = $configuration->get('dispatcher-cache-enabled');

        $ttl = $configuration->get('dispatcher-cache-ttl');

        if ( $ttl !== null && !defined('COMODOJO_CACHE_DEFAULT_TTL') ) {

            define('COMODOJO_CACHE_DEFAULT_TTL', $ttl);

        }

        $folder = $configuration->get('dispatcher-cache-folder');

        $algorithm = self::getAlgorithm( $configuration->get('dispatcher-cache-algorithm') );

        $manager = new CacheManager( $algorithm );

        if ( $enabled === true ) {

            $manager->addProvider( new FileCache($folder) );

        }

        return $manager;

    }

    /**
     * Map provided log level to level code
     *
     * @param   string    $algorithm
     *
     * @return  integer
     */
    protected static function getAlgorithm($algorithm) {

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
