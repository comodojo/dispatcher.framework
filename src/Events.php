<?php namespace Comodojo\Dispatcher;

/**
 * Dispatcher event class
 *
 * It handle any hook fired by events.
 *
 * By default, dispatcher uses three different level of events:
 *
 * - level one: [event]
 *
 * - level two: [event].[notification]
 *
 * - level one: [event].[notification].[detail]
 *
 *
 * There is only one level 1 event, the "dispatcher". It can be used, for example,
 *
 * to close dispatcher for a particular kind of request.
 *
 * Level 2/3 events are stage-dependent. A complete, time based, list could be:
 *
 *
 * << dispatcher receive a request, so starts modelling it in ObjectRequest
 *
 * -->> "dispatcher.request"
 *
 * ---->> "dispatcher.request.[METHOD]"
 *
 * ---->> "dispatcher.request.[SERVICE]"
 *
 * -->> "dispatcher.request.#" (special event, for tracing or timing funcs; fires after every other callback and cannot modify request)
 *
 *
 * >< an instance of routingtable was initiated
 *
 * -->> "dispatcher.routingtable" - exposing routingtable
 *
 *
 * >< ask routing table for a route
 *
 * -->> "dispatcher.serviceroute" - exposing serive route
 *
 * ---->> "dispatcher.serviceroute.[TYPE]"
 *
 * ---->> "dispatcher.serviceroute.[SERVICE]"
 *
 * -->> "dispatcher.serviceroute.#" (special event, for tracing or timing funcs; fires after every other callback and cannot modify route)
 *
 *
 * >< service runs and return a result || route is a REDIRECT || route is a ERROR
 *
 * -->> "dispatcher.result" - exposing result
 *
 * -->> "dispatcher.route|redirect|error"
 *
 * ---->> "dispatcher.route|redirect|error.[STATUSCODE]"
 *
 * -->> "dispatcher.result.#" (special event, for tracing or timing funcs; fires after every other callback and can modify result)
 *
 *
 * >> result to requestor
 *
 * @package     Comodojo dispatcher (Spare Parts)
 * @author      Marco Giovinazzi <info@comodojo.org>
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

class Events {

    /**
     * Hooks database (a simple array!).
     *
     * @var     array
     */
    private $hooks = Array();

    /**
     * Logger, injected by dispatcher
     *
     * @var float
     */
    private $logger = null;

    /**
     * Events constructor
     *
     * It does nothing special: called at boot time, only notify that events
     * are ready.
     *
     * @return null
     */
    final public function __construct($logger) {

        $this->logger = $logger;

    }

    /**
     * Add an event
     *
     * It link the $event to $callback that will we executed once event will be called.
     * Callback can also be addressed via $class->$method using third parameter
     * ($method).
     *
     * @param   string  $event      Event name
     * @param   string  $callback   Callback (or callback class)
     * @param   string  $method     (optional) callback method
     *
     * @return  Object  $this
     */
    final public function add($event, $callback, $method=null) {

        if ( is_null($method) ) {

            if ( isset($this->hooks[$event]) ) array_push($this->hooks[$event], $callback);

            else $this->hooks[$event] = Array($callback);

        }

        else {

            if ( isset($this->hooks[$event]) ) array_push($this->hooks[$event], Array($callback, $method));

            else $this->hooks[$event] = Array(Array($callback, $method));

        }

        return $this;

    }

    /**
     * Remove an event
     *
     * If optional parameter $callback is provided, only the event referring this
     * callback (or callback class) will be removed. Otherwise, any callback related
     * to event will be deleted.
     *
     * @param   string  $event      Event name
     * @param   string  $callback   Callback (or callback class)
     *
     * @return  bool
     */
    final public function remove($event, $callback=null) {

        if ( is_null($callback) AND isset($this->hooks[$event]) ) {

            unset($this->hooks[$event]);

            return true;

        }

        else if ( isset($this->hooks[$event]) ) {

            foreach ($this->hooks[$event] as $key => $hook) {

                if ( is_array($hook) ) {

                    if ( $hook[0] == $callback ) {

                        unset($this->hooks[$event][$key]);

                        return true;

                    }

                }
                else {

                    if ( $hook == $callback ) {

                        unset($this->hooks[$event][$key]);

                        return true;

                    }

                }

            }

            return false;

        }

        else return false;

    }

    /**
     * Fire an event
     *
     * @param   string  $event      Event name
     * @param   string  $type       the type of event
     * @param   Object  $data       Data to provide to callback
     *
     * @return  Object|null
     */
    final public function fire($event, $type, $data) {

        $this->logger->info('Firing event', array(
            'EVENT' =>  $event
        ));

        $value = $data;

        if ( isset($this->hooks[$event]) ) {

            foreach($this->hooks[$event] as $callback) {

                $return_value = null;

                if ( is_array($callback) ) {

                    if ( is_callable(Array($callback[0], $callback[1])) ) {

                        try {

                            $return_value = call_user_func(Array($callback[0], $callback[1]), $value);

                        } catch (Exception $e) {

                            $this->logger->error('Hook error', array(
                                'EVENT'    => $event,
                                'CALLBACK' => $callback[0],
                                'METHOD'   => $callback[1],
                                'CODE'     => $e->getCode(),
                                'MESSAGE'  => $e->getMessage()
                            ));

                            continue;

                        }

                    }
                    else {

                        $this->logger->warning('Skipping not-callable hook', array(
                            'EVENT'    => $event,
                            'CALLBACK' => $callback[0],
                            'METHOD'   => $callback[1]
                        ));

                        debug("Skipping not-callable hook ".$event."::".$callback[0].":".$callback[1], "WARNING", "events");
                        continue;

                    }

                }
                else {

                    if ( is_callable($callback) ) {

                        try {

                            $return_value = call_user_func($callback, $value);

                        } catch (Exception $e) {

                            $this->logger->error('Hook error', array(
                                'EVENT'    => $event,
                                'CALLBACK' => $callback,
                                'CODE'     => $e->getCode(),
                                'MESSAGE'  => $e->getMessage()
                            ));

                            continue;

                        }

                    }
                    else {

                        $this->logger->warning('Skipping not-callable hook', array(
                            'EVENT'    => $event,
                            'CALLBACK' => $callback
                        ));

                        continue;

                    }

                }

                switch ($type) {

                    case 'DISPATCHER':

                    $value = is_bool($return_value) ? $return_value : $value;

                    break;

                    case 'REQUEST':

                    $value = $return_value instanceof \Comodojo\Dispatcher\ObjectRequest\ObjectRequest ? $return_value : $value;

                    break;

                    case 'TABLE':

                    $value = $return_value instanceof \Comodojo\Dispatcher\ObjectRoutingTable\ObjectRoutingTable ? $return_value : $value;

                    break;

                    case 'ROUTE':

                    $value = $return_value instanceof \Comodojo\Dispatcher\ObjectRoute\ObjectRoute ? $return_value : $value;

                    break;

                    case 'RESULT':

                    $value = $return_value instanceof \Comodojo\Dispatcher\ObjectResult\ObjectResultInterface ? $return_value : $value;

                    break;

                    case 'VOID':
                    default:

                    $value = $value;

                    break;

                }

            }

            return $value;

        }

        return $data;

    }

}