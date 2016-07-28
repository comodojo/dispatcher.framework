<?php namespace Comodojo\Dispatcher\Components;

use \Psr\Log\LoggerInterface;
use \League\Event\Emitter;
use \League\Event\ListenerInterface;

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


class EventsManager extends Emitter {

    public $logger;

    public function __construct(LoggerInterface $logger) {

        $this->logger = $logger;

    }

    public function subscribe($event, $class, $priority = 0) {

        $callable = $this->convertToListener($class, $event);

        if ( $callable === false ) return null;

        return $this->addListener($event, $callable, $priority);

    }

    public function subscribeOnce($event, $class, $priority = 0) {

        $callable = $this->convertToListener($class, $event);

        if ( $callable === false ) return null;

        return $this->addOneTimeListener($event, $callable, $priority);

    }

    protected function convertToListener($class, $event) {

        if ( !class_exists($class) ) {

            $this->logger->error("Cannot subscribe class $class to event $event: cannot find class");

            return false;

        }

        $callable = new $class();

        if ($callable instanceof ListenerInterface) {

            $this->logger->debug("Subscribing handler $class to event $event");

            return $callable;

        }

        $this->logger->error("Cannot subscribe class $class to event $event: class is not an instance of \League\Event\ListenerInterface");

        return false;

    }

    public function load($plugins) {

        if ( !empty($plugins) ) {

            foreach( $plugins as $name => $plugin ) {

                if ( !isset($plugin['class']) || !isset($plugin["event"]) ) {

                    $this->logger->error("Invalid plugin definition", $plugin);

                    continue;

                }

                $priority = isset($plugin['priority']) ? $plugin['priority'] : 0;
                $onetime = isset($plugin['onetime']) ? $plugin['onetime'] : 0;

                if ( $onetime ) $this->subscribeOnce($plugin['event'], $plugin['class'], $priority);
                else $this->subscribe($plugin['event'], $plugin['class'], $priority);

            }

        }

    }

}
