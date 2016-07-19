<?php namespace Comodojo\Dispatcher\Components;

use \Monolog\Logger;
use \League\Event\Emitter;

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

    protected $logger;

    public function __construct(Logger $logger) {

        $this->logger = $logger;

    }

    public function logger() {

        return $this->logger;

    }

    public function subscribe($event, $class, $method = null, $priority = 0) {

        $callable = ( is_null($method) ) ? $class : array($class, $method);

        $this->logger->debug("Subscribing handler $calss to event $event", array(
            "CALLABLE" => $callable,
            "PRIORITY" => $priority
        ));

        return $this->addListener($event, $callable, $priority);

    }

    public function subscribeOnce($event, $class, $method = null, $priority = 0) {

        $callable = ( is_null($method) ) ? $class : array($class, $method);

        $this->logger->debug("Subscribing once handler $calss to event $event", array(
            "CALLABLE" => $callable,
            "PRIORITY" => $priority
        ));

        return $this->addOneTimeListener($event, $callable, $priority);

    }

    public function unsubscribe($event, $class = null, $method = null) {

        if ( is_null($class) ) {

            $this->logger->debug("Unsubscribing all handlers from event $event");

            return $this->removeAllListeners($event);

        } else {

            $callable = ( is_null($method) ) ? $class : array($class, $method);

            $this->logger->debug("Unsubscribing handler $calss from event $event", array(
                "CALLABLE" => $callable
            ));

            return $this->removeListener($event, $callable);

        }

    }

    public function load($plugins) {

        if ( !empty($plugins) ) {

            foreach( $plugins as $name => $event ) {

                $callable = ( is_null($event['method']) ) ? $event["class"] : array($event["class"], $event["method"]);

                $this->addListener($event["event"], $callable, $event["priority"]);

            }

        }

        return $this;

    }

}
