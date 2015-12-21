<?php namespace Comodojo\Dispatcher;

use \Comodojo\Dispatcher\Debug;
use \Comodojo\Exception\DispatcherException;
use \Comodojo\Exception\IOException;
use \Exception;
use \Comodojo\Dispatcher\ObjectRequest\ObjectRequest;
use \Comodojo\Dispatcher\ObjectRoutingTable\ObjectRoutingTable;
use \Comodojo\Dispatcher\ObjectRoute\ObjectRoute;
use \Comodojo\Dispatcher\ObjectResult\ObjectResultInterface;
use \Comodojo\Dispatcher\ObjectResult\ObjectSuccess;
use \Comodojo\Dispatcher\ObjectResult\ObjectError;
use \Comodojo\Dispatcher\ObjectResult\ObjectRedirect;

/**
 * THE comodojo dispatcher
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

class Dispatcher {

    /**
     * Is dispatcher enabled?
     *
     * @var bool
     */
    private $enabled = DISPATCHER_ENABLED;

    /**
     * Time at the time of request
     *
     * @var float
     */
    private $current_time = null;

    /**
     * Working mode (rewrite/standard)
     *
     * @var string
     */
    private $working_mode = 'STANDARD';

    /**
     * Service URI
     *
     * @var string
     */
    private $service_uri = null;

    /**
     * Service URL
     *
     * @var string
     */
    private $service_url = null;

    /**
     * Request method (HTTP)
     *
     * @var string
     */
    private $request_method = null;

    /**
     * Cache switcher: if true, cacher will not re-cache current response
     *
     * @var bool
     */
    private $result_comes_from_cache = false;

    /**
     * Container for ObjectRequest
     *
     * @var Object|null
     */
    private $request = null;

    /**
     * Container for ObjectRoutingTable
     *
     * @var Object|null
     */
    private $routingtable = null;

    /**
     * Container for ObjectRoute
     *
     * @var Object|null
     */
    private $serviceroute = null;

    // ###### Helpers ###### //

    /**
     * Do server-side caching
     *
     * @var Object|null
     */
    private $cacher = null;

    /**
     * Logging facilities
     *
     * @var Object|null
     */
    private $logger = null;

    /**
     * Do headers manipulations
     *
     * @var Object|null
     */
    private $header = null;

    /**
     * Dispatch events
     *
     * @var Object|null
     */
    private $events = null;

    /**
     * Constructor method
     *
     * It reads request and transforms it in a modeled ObjectRequest.
     *
     * @return null
     */
    final public function __construct() {

        ob_start();

        // Now start to build dispatcher instance

        $this->current_time = microtime(true);

        $this->working_mode = $this->getWorkingMode();

        $this->service_uri = $this->urlGetUri();

        $this->service_url = $this->urlGetUrl();

        $this->request_method = $_SERVER['REQUEST_METHOD'];

        // Init logger
        $this->logger = new Debug();

        $this->logger->info('Dispatcher online, request time: '.$this->current_time);

        $this->logger->debug('Working mode: '.$this->working_mode);

        $this->logger->debug('Request URI: '.$this->service_uri);

        // Init routing table
        $this->routingtable = new ObjectRoutingTable();

        // Init components
        $this->cacher = new Cache($this->current_time, $this->logger);

        $this->header = new Header($this->current_time);

        $this->events = new Events($this->logger);

        // Starts composing request object (ObjectRequest)

        list($request_service,$request_attributes) = $this->urlInterpreter($this->working_mode);

        list($request_parameters, $request_raw_parameters) = $this->deserializeParameters($this->request_method);

        $this->logger->debug('Provided attributes',$request_attributes);

        $this->logger->debug('Provided parameters',$request_parameters);

        $request_headers = $this->header->getRequestHeaders();

        // Before composing the object request, remember to define the current (absolute) dispatcher baseurl
        // (if not specified in dispatcher-config)
        if ( !defined("DISPATCHER_BASEURL") ) define("DISPATCHER_BASEURL",$this->urlGetAbsolute($request_service));

        // Now let's compose request object

        $this->request = new ObjectRequest();

        $this->request
            ->setCurrentTime($this->current_time)
            ->setService($request_service)
            ->setMethod($this->request_method)
            ->setAttributes($request_attributes)
            ->setParameters($request_parameters)
            ->setRawParameters($request_raw_parameters)
            ->setHeaders($request_headers);

        $this->logger->info('Requested service: '.$request_service);
        $this->logger->info('Request HTTP method: '.$this->request_method);

    }

    /**
     * Set punctual service route
     *
     * @param   string  $service    Service name (src)
     * @param   string  $type       Route type (ROUTE, REDIRECT or ERROR)
     * @param   string  $target     Service target (dst)
     * @param   array   $parameters (optional) Service options (cache, ...)
     * @param   bool    $relative   (optional) If true, target will be assumed in default service directory
     */
    final public function setRoute($service, $type, $target, $parameters=array(), $relative=true) {

        try {

            if ( strtoupper($type) == "ROUTE" ) {

                if ( $relative ) $this->routingtable->setRoute($service, $type, DISPATCHER_SERVICES_FOLDER.$target, $parameters);

                else $this->routingtable->setRoute($service, $type, $target, $parameters);

            }

            else if ( strtoupper($type) == "REDIRECT" ) {

                if ( $relative ) $this->routingtable->setRoute($service, $type, DISPATCHER_BASEURL.$target, $parameters);

                else $this->routingtable->setRoute($service, $type, $target, $parameters);

            }

            else $this->routingtable->setRoute($service, $type, $target, $parameters);

        } catch (Exception $e) {

            //debug error but do not stop dispatcher
            $this->logger->warning( 'Unable to set route', array('SERVIVE' => $service) );

        }

    }

    /**
     * Unset punctual service route
     *
     * @param   string  $service    Service name (src)
     *
     * @return null
     */
    final public function unsetRoute($service) {

        try {

            $this->routingtable->unsetRoute($service);

        } catch (Exception $e) {

            //debug error but do not stop dispatcher
            $this->logger->warning( 'Unable to unset route', array('SERVIVE' => $service) );

        }

    }

    /**
     * Add hook for an event
     *
     * @param   string  $event      The event name
     * @param   string  $callback   The callback (or class if $method is specified)
     * @param   string  $method     (optional) Method for $callback
     */
    final public function addHook($event, $callback, $method=null) {

        try {

            $this->events->add($event, $callback, $method);

        } catch (Exception $e) {

            //debug error but do not stop dispatcher
            $this->logger->warning( 'Unable to add hook', array(
                'CALLBACK' => $callback,
                'METHOD' => $method,
                'EVENT' => $event
            ) );

        }

    }

    /**
     * Remove an hook
     *
     * @param   string  $event      The event name
     * @param   string  $callback   The callback (or class if $method is specified)
     */
    final public function removeHook($event, $callback=null) {

        try {

            $this->events->remove($event, $callback);

        } catch (Exception $e) {

            //debug error but do not stop dispatcher
            $this->logger->warning( 'Unable to remove hook', array(
                'CALLBACK' => $callback,
                'EVENT' => $event
            ) );

        }

    }

    /**
     * Include a plugin
     *
     * @param   string  $plugin     The plugin name
     * @param   string  $folder     (optional) plugin folder (if omitted, dispatcher will use default one)
     */
    final public function loadPlugin($plugin, $folder=DISPATCHER_PLUGINS_FOLDER) {

        include $folder.$plugin.".php";

    }

    /**
     * Get current time (time at the time of the request)
     *
     * @return  float   time in microsec
     */
    final public function getCurrentTime() {

        return $this->current_time;

    }

    /**
     * Get dispatcher logger
     *
     * @return  Object
     */
    final public function getLogger() {

        return $this->logger;

    }

    /**
     * Crear cache for a single request or entirely
     *
     * @param   mixed    $all    True = purge entire cache, false/null = purge current request cache
     *
     * @return  Object
     */
    final public function clearCache($all = null) {

        return $this->cacher->purge( $all == true ? null : $this->service_url );

    }

    /**
     * Dispatcher working method
     *
     * @return  mixed
     */
    final public function dispatch() {

        // Before building dispatcher instance, fire THE level1 event "dispatcher"
        // This is the only way (out of dispatcher-config) to disable dispatcher

        $fork = $this->events->fire("dispatcher", "VOID", $this);

        // if ( is_bool($fork)  ) $this->enabled = $fork;

        // After building dispatcher instance, fire THE level2 event "dispatcher.request"
        // This default hook will expose current request (ObjectRequest) to callbacks

        $fork = $this->events->fire("dispatcher.request", "REQUEST", $this->request);

        if ( $fork instanceof \Comodojo\Dispatcher\ObjectRequest\ObjectRequest ) $this->request = $fork;

        // Fire level3 event "dispatcher.request.[method]"

        $fork = $this->events->fire("dispatcher.request.".$this->request_method, "REQUEST", $this->request);

        if ( $fork instanceof \Comodojo\Dispatcher\ObjectRequest\ObjectRequest ) $this->request = $fork;

        // Fire level3 event "dispatcher.request.[service]"

        $fork = $this->events->fire("dispatcher.request.".$this->request->getService(), "REQUEST", $this->request);

        if ( $fork instanceof \Comodojo\Dispatcher\ObjectRequest\ObjectRequest ) $this->request = $fork;

        // Fire special event, it will not modify request

        $this->events->fire("dispatcher.request.#", "VOID", $this->request);

        // Check if dispatcher is enabled

        if ( $this->enabled == false ) {

            $route = new ObjectError();
            $route->setStatusCode(503);

            $return = $this->route($route);

            $this->logger->info('Shutting down dispatcher (administratively disabled)');

            ob_end_clean();

            return $return;

        }

        // Before calculating service route, expose the routing table via level2 event "dispatcher.routingtable"

        $fork = $this->events->fire("dispatcher.routingtable", "TABLE", $this->routingtable);

        if ( $fork instanceof \Comodojo\Dispatcher\ObjectRoutingTable\ObjectRoutingTable ) $this->routingtable = $fork;

        // Retrieve current route from routing table

        $service = $this->request->getService();

        $this->logger->debug('Querying routingtable for service route', array(
            'SERVICE' => $service
        ));

        $preroute = $this->routingtable->getRoute($service);

        $this->serviceroute = new ObjectRoute();
        $this->serviceroute->setService($service)
            ->setType($preroute["type"])
            ->setTarget($preroute["target"]);

        if ( isset($preroute["parameters"]["class"]) ) {
            $this->serviceroute->setClass($preroute["parameters"]["class"]);
        } else {
            $t = pathinfo($preroute["target"]);
            $this->serviceroute->setClass(preg_replace('/\\.[^.\\s]{3,4}$/', '', $t["filename"]));
        }

        if ( isset($preroute["parameters"]["redirectCode"]) ) {
            $this->serviceroute->setRedirectCode($preroute["parameters"]["redirectCode"]);
            unset($preroute["parameters"]["redirectCode"]);
        }
        if ( isset($preroute["parameters"]["errorCode"]) ) {
            $this->serviceroute->setErrorCode($preroute["parameters"]["errorCode"]);
            unset($preroute["parameters"]["errorCode"]);
        }
        if ( isset($preroute["parameters"]["cache"]) ) {
            $this->serviceroute->setCache($preroute["parameters"]["cache"]);
            unset($preroute["parameters"]["cache"]);
        }
        if ( isset($preroute["parameters"]["ttl"]) ) {
            $this->serviceroute->setTtl($preroute["parameters"]["ttl"]);
            unset($preroute["parameters"]["ttl"]);
        }
        if ( isset($preroute["parameters"]["headers"]) ) {
            if ( is_array($preroute["parameters"]["headers"]) ) foreach ($preroute["parameters"]["headers"] as $header => $value) $this->serviceroute->setHeader($header, $value);
            unset($preroute["parameters"]["headers"]);
        }
        if ( isset($preroute["parameters"]["accessControl"]) ) {
            $this->serviceroute->setRedirectCode($preroute["parameters"]["accessControl"]);
            unset($preroute["parameters"]["accessControl"]);
        }

        foreach ($preroute["parameters"] as $parameter => $value) {
            $this->serviceroute->setParameter($parameter, $value);
        }

        // Now that we have a route, fire the level2 event "dispatcher.serviceroute"
        // and level3 events:
        // - "dispatcher.serviceroute.[type]"
        // - "dispatcher.serviceroute.[service]"

        $fork = $this->events->fire("dispatcher.serviceroute", "ROUTE", $this->serviceroute);

        if ( $fork instanceof \Comodojo\Dispatcher\ObjectRoute\ObjectRoute ) $this->serviceroute = $fork;

        $fork = $this->events->fire("dispatcher.serviceroute.".$this->serviceroute->getType(), "ROUTE", $this->serviceroute);

        if ( $fork instanceof \Comodojo\Dispatcher\ObjectRoute\ObjectRoute ) $this->serviceroute = $fork;

        $fork = $this->events->fire("dispatcher.serviceroute.".$this->serviceroute->getService(), "ROUTE", $this->serviceroute);

        if ( $fork instanceof \Comodojo\Dispatcher\ObjectRoute\ObjectRoute ) $this->serviceroute = $fork;

        // Fire special event, it will not modify route

        $this->events->fire("dispatcher.serviceroute.#", "VOID", $this->serviceroute);

        // Before using route to handle request, check if access control should block instance

        $accesscontrol = preg_replace('/\s+/', '', $this->serviceroute->getAccessControl());

        if ( $accesscontrol != null AND $accesscontrol != "*" ) {

            $origins = explode(",", $accesscontrol);

            if ( !in_array(@$_SERVER['HTTP_ORIGIN'], $origins) ) {

                $route = new ObjectError();
                $route->setStatusCode(403)->setContent("Origin not allowed");

                $return = $this->route($route);

                ob_end_clean();

                return $return;

            }

        }

        $this->logger->debug('Service route acquired', array(
            'SERVICE'       => $this->serviceroute->getService(),
            'TYPE'          => $this->serviceroute->getType(),
            'CLASS'         => $this->serviceroute->getClass(),
            'REDIRECTCODE'  => $this->serviceroute->getRedirectCode(),
            'ERRORCODE'     => $this->serviceroute->getErrorCode(),
            'CACHE'         => $this->serviceroute->getCache(),
            'TTL'           => $this->serviceroute->getTtl(),
            'HEADERS'       => $this->serviceroute->getHeaders(),
            'REDIRECTCODE'  => $this->serviceroute->getRedirectCode()
        ));

        switch($this->serviceroute->getType()) {

            case "ERROR":

                $route = new ObjectError();
                $route->setService($this->serviceroute->getService())
                    ->setStatusCode($this->serviceroute->getErrorCode())
                    ->setContent($this->serviceroute->getTarget())
                    ->setHeaders($this->serviceroute->getHeaders());

                break;

            case "REDIRECT":

                $route = new ObjectRedirect();
                $route->setService($this->serviceroute->getService())
                    ->setStatusCode($this->serviceroute->getRedirectCode())
                    ->setLocation($this->serviceroute->getTarget())
                    ->setHeaders($this->serviceroute->getHeaders());

                break;

            case "ROUTE":

                try {

                    $route = $this->runService($this->request, $this->serviceroute);

                } catch (DispatcherException $de) {

                    $this->logger->error('Service returns a DispatcherException', array(
                        'SERVICE' => $this->serviceroute->getService(),
                        'CODE'    => $de->getCode(),
                        'MESSAGE' => $de->getMessage()
                    ));

                    $route = new ObjectError();
                    $route->setService($this->serviceroute->getService())
                        ->setStatusCode($de->getCode())
                        ->setContent($de->getMessage());

                } catch (Exception $e) {

                    $this->logger->error('Error processing service', array(
                        'SERVICE' => $this->serviceroute->getService(),
                        'CODE'    => $e->getCode(),
                        'MESSAGE' => $e->getMessage()
                    ));

                    $route = new ObjectError();
                    $route->setService($this->serviceroute->getService())
                        ->setStatusCode(500)
                        ->setContent($e->getMessage());

                }

                break;

        }

        $return = $this->route($route);

        ob_end_clean();

        return $return;

    }

    /**
     * Url interpreter
     *
     * Starting from $workingMode (REWRITE|STANDARD) will acquire service route from request.
     *
     * In other words, will separate service and attributes and populate class parameters
     * service_requested and service_attributes
     *
     * @param   string  $workingMode    (REWRITE|STANDARD)
     */
    private function urlInterpreter($workingMode) {

        if ($workingMode == "REWRITE") {

            $uri = explode('/', $_SERVER['REQUEST_URI']);
            $scr = explode('/', $_SERVER['SCRIPT_NAME']);

            for($i= 0;$i < sizeof($scr);$i++) {
                if ($uri[$i] == $scr[$i]) unset($uri[$i]);
            }

            $service_matrix = array_values($uri);

            if (isset($service_matrix[0])) {

                $service_requested = $service_matrix[0];

                $last = $service_matrix[sizeof($service_matrix)-1];

                $service_attributes = empty($last) ? array_slice($service_matrix, 1, -1) : array_slice($service_matrix, 1);

            }
            else {

                $service_requested = "default";
                $service_attributes = array();

            }

        }
        else {

            $service_matrix = $_GET;

            if (isset($service_matrix["service"])) {

                $service_requested = $service_matrix["service"];
                unset($service_matrix["service"]);
                $service_attributes = $service_matrix;

            }
            else {

                $service_requested = "";
                $service_attributes = array();

            }

        }

        return array($service_requested, $service_attributes);

    }

    /**
     * Return current request uri
     *
     * @return string  The request uri
     */
    private function urlGetUri() {

        return $_SERVER['REQUEST_URI'];

    }

    /**
     * Return current request url
     *
     * @return string  The request uri
     */
    private function urlGetUrl() {

        return $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

    }

    /**
     * Return dispatcher baseurl, no matter the request
     *
     * @return uri  The baseurl
     */
    private function urlGetAbsolute($service=null) {

        $http = 'http' . ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 's' : '') . '://';

        if ( is_null($service) ) $uri = "";

        else {

            $self = $_SERVER['PHP_SELF'];

            $uri = preg_replace("/\/index.php(.*?)$/i","",$self);

        }

        return ( $http . $_SERVER['HTTP_HOST'] . $uri . "/" );

    }

    /**
     * Get current working mode
     *
     * @return  string  "REWRITE" or "STANDARD"
     */
    private function getWorkingMode() {

        return DISPATCHER_USE_REWRITE ? "REWRITE" : "STANDARD";

    }

    /**
     * Retrieve parameters from input
     *
     * @return  array
     */
    private function deserializeParameters($method) {

        $parameters = array();

        switch($method) {

            case 'POST':

            $parameters = $_POST;

            break;

            case 'PUT':
            case 'DELETE':

            parse_str(file_get_contents('php://input'), $parameters);

            break;

        }

        return array($parameters, file_get_contents('php://input'));

    }

    private function attributesMatch($provided, $expected, $liked) {

        if ( $this->working_mode == "STANDARD" ) return $this->parametersMatch($provided, $expected, $liked);

        $attributes = array();

        $psize = sizeof($provided);
        $esize = sizeof($expected);
        $lsize = sizeof($liked);

        if ( $psize < $esize ) throw new DispatcherException("Conversation error", 400);

        else if ( $psize == $esize ) {

            $attributes = $psize == 0 ? array() : array_combine($expected, $provided);

        }

        else {

            if ( $esize == 0 ) {

                $e_attributes = array();

                $lvalues = $provided;

            } else {

                $pvalues = array_slice($provided, 0, $esize);

                $e_attributes = array_combine($expected, $pvalues);

                $lvalues = array_slice($provided, $esize);

            }

            $lvaluessize = sizeof($lvalues);

            $l_attributes = array();

            if ( $lvaluessize < $lsize ) {

                $l_attributes = array_combine(array_slice($liked, 0, $lvaluessize), $lvalues);

            }
            else if ( $lvaluessize == $lsize ) {

                $l_attributes = $lvaluessize == 0 ? array() : array_combine($liked, $lvalues);

            }
            else {

                if ( $lsize == 0 ) {

                    $l_attributes = $lvalues;

                } else {

                    $r_attributes = array_combine($liked, array_slice($lvalues, 0, $lsize));

                    $remaining_parameters = array_slice($lvalues, $lsize);

                    $l_attributes = array_merge($r_attributes, $remaining_parameters);

                }

            }

            $attributes = array_merge($e_attributes, $l_attributes);

        }

        return $attributes;

    }

    private function parametersMatch($provided, $expected, $liked) {

        foreach ($expected as $parameter) {

            if ( !isset($provided[$parameter]) ) throw new DispatcherException("Conversation error", 400);

        }

        return $provided;

    }

    private function runService(ObjectRequest $request, ObjectRoute $route) {

        $method = $request->getMethod();
        $service = $route->getService();
        $cache = $route->getCache();
        $ttl = $route->getTtl();
        $target = $route->getTarget();

        // First of all, check cache (in case of GET request)

        if ( $method == "GET" AND ( $cache == "SERVER" OR $cache == "BOTH" ) ) {

            $from_cache = $this->cacher->get($this->service_url, $ttl);

            if ( is_array($from_cache) ) {

                $maxage = $from_cache["maxage"];
                $bestbefore = $from_cache["bestbefore"];
                $result = $from_cache["object"];

                // Publish that result comes from cache (so will not be re-cached)

                $this->result_comes_from_cache = true;

                return $result;

            }

        }

        // If there's no cache for this request, use routing information to find service

        if ( (include($target)) === false ) throw new DispatcherException("Cannot run service", 500);

        // Find a service implementation and try to init it

        $service_class = $route->getClass();

        if ( empty($service_class) ) throw new DispatcherException("Cannot run service", 500);

        $service_class = "\\Comodojo\\Dispatcher\\Service\\".$service_class;

        // Setup service

        try {

            $theservice = new $service_class($this->logger, $this->cacher);

            $theservice->setup();

        } catch (Exception $e) {

            throw $e;

        }

        // Check if service supports current HTTP method

        if ( !in_array($method, explode(",", $theservice->getSupportedMethods())) ) {

            throw new DispatcherException("Allow: ".$theservice->getSupportedMethods(), 405);

        }

        // Check if service implements current HTTP method

        if ( !in_array($method, $theservice->getImplementedMethods()) ) {

            throw new DispatcherException("Allow: ".implode(",",$theservice->getImplementedMethods()), 501);

        }

        // Match attributes and parameters

        list($expected_attributes, $expected_parameters) = $theservice->getExpected($method);

        list($liked_attributes, $liked_parameters) = $theservice->getLiked($method);

        try {

            $validated_attributes = $this->attributesMatch($request->getAttributes(), $expected_attributes, $liked_attributes);

            $validated_parameters = $this->parametersMatch($request->getParameters(), $expected_parameters, $liked_parameters);

        } catch (DispatcherException $de) {

            throw $de;

        }

        // Fill service with dispatcher pieces

        $theservice->setAttributes($validated_attributes);
        $theservice->setParameters($validated_parameters);
        $theservice->setRawParameters($request->getRawParameters());
        $theservice->setRequestHeaders($request->getHeaders());

        // Requesto to service the callable method (just to handle any method)

        $current_method = $theservice->getCallableMethod($method);

        // Finally run service method and catch exceptions

        try {

            $result = $theservice->$current_method();

            $return = new ObjectSuccess();
            $return->setService($service)
                ->setStatusCode($theservice->getStatusCode())
                ->setContent($result)
                ->setHeaders( array_merge($theservice->getHeaders(), $route->getHeaders()) )
                ->setContentType($theservice->getContentType())
                ->setCharset($theservice->getCharset());

        } catch (DispatcherException $de) {

            throw $de;

        } catch (Exception $e) {

            throw $e;

        }

        return $return;

    }

    /**
     * Route request handling ObjectResult hooks
     *
     * @param   ObjectResult    $route  An implementation of ObjectResultInterface
     * @return  string                  Content (stuff that will go on screen)
     */
    private function route(ObjectResultInterface $route) {

        // Starting from the routing instance, select the relative level2 hook
        // This means event engine will fire a dispatcher.[routetype] event
        // In case of wrong instance, create an ObjectError (500, null) instance

        if ( $route instanceof ObjectSuccess ) {

            $hook = "dispatcher.route";

        } else if ( $route instanceof ObjectError ) {

            $hook = "dispatcher.error";

        } else if ( $route instanceof ObjectRedirect ) {

            $hook = "dispatcher.redirect";

        } else {

            $hook = "dispatcher.error";

            $route = new ObjectError();

        }

        // Fire first hook, a generic "dispatcher.result", Object Type independent

        $fork = $this->events->fire("dispatcher.result", "RESULT", $route);

        if ( $fork instanceof \Comodojo\Dispatcher\ObjectResult\ObjectResultInterface ) $route = $fork;

        // Fire second hook (level2), as specified above

        $fork = $this->events->fire($hook, "RESULT", $route);

        if ( $fork instanceof \Comodojo\Dispatcher\ObjectResult\ObjectResultInterface ) $route = $fork;

        // Now select and fire last hook (level3)
        // This means that event engine will fire something like "dispatcher.route.200"
        // or "dispatcher.error.500"

        $fork = $this->events->fire($hook.".".$route->getStatusCode(), "RESULT", $route);

        if ( $fork instanceof \Comodojo\Dispatcher\ObjectResult\ObjectResultInterface ) $route = $fork;

        // Fire special event, it may modify result

        $fork = $this->events->fire("dispatcher.result.#", "RESULT", $route);

        if ( $fork instanceof \Comodojo\Dispatcher\ObjectResult\ObjectResultInterface ) $route = $fork;

        // After hooks:
        // - store cache
        // - start composing header
        // - return result

        $cache = $route instanceof \Comodojo\Dispatcher\ObjectResult\ObjectSuccess ? $this->serviceroute->getCache() : null;

        if ( $this->request_method == "GET" AND
            ( $cache == "SERVER" OR $cache == "BOTH" ) AND
            $this->result_comes_from_cache == false AND
            $route instanceof \Comodojo\Dispatcher\ObjectResult\ObjectSuccess )
        {

            $this->cacher->set($this->service_url, $route);

        }


        $this->header->free();

        if ( $cache == "CLIENT" OR $cache == "BOTH" ) $this->header->setClientCache($this->serviceroute->getTtl());

        $this->header->setContentType($route->getContentType(), $route->getCharset());

        foreach ($route->getHeaders() as $header => $value) {

            $this->header->set($header, $value);

        }

        $message = $route->getContent();

        $this->header->compose($route->getStatusCode(), strlen($message), $route->getLocation()); # ><!/\!°>

        // Return the content (stuff that will go on screen)

        return $message;

    }

}
