Routing requests
================

.. _dispatcher.plugin.test: https://github.com/comodojo/dispatcher.plugin.test

Dispatcher includes a minimal embedded URL router that maps urls to services, using an approach slightly different from the one used in other common framework.

Instead of map methods (or functions) to full uris (plus HTTP verbs), dispatcher retrieves routes using only the first path in the URI. In practice, this variable *select* the route the request will follow.

This kind of next-hop approach leaves all logic to the service itself (for example, the ability to serve POST requests instead of GET), decoupling routing and service processing.

.. note:: To understand how to handle HTTP methods jump to the service section.

mod_rewrite
***********

Dispatcher uses the apache mod_rewrite extension to acquire requests and route those to relative services.

It is also possible to disable this feature and force dispatcher to reply only to requests directed to index.php, settting the
`DISPATCHER_USE_REWRITE` constant to false.

In this case, parameters should be passed as a standard http query-string *key=value" pairs.

Attributes and Parameters
*************************

TBW

Defining routes
***************

To define a new route, the `setRoute()` method should be invoked before the `dispatch()`.

Syntax of method is::

    setRoute( [route], [type], [target], [parameters], [relative] )

So, an example route could be::

    $dispatcher->setRoute("helloworld", "ROUTE", "HelloWorld.php", array(), true);

Predefined routes
*****************

The router supports 2 special routes:

- Landing route *""* (empty string)
- Default route "default"

Only the default route is initially defined and lands to a 404 "Service not found" error.

Autorouting
***********

If enabled setting constant `DISPATCHER_AUTO_ROUTE`, dispatcher will try to map requests to service files using file names.

Only files in the `DISPATCHER_SERVICES_FOLDER` are taken into account.

Conditional routing
*******************

Thanks to the event subsystem, dispatcher can force or totally override the routing logic.

This snippet (from `dispatcher.plugin.test`_) simply change the target service if a special request header is provided.::

    public static function conditional_routing_header($ObjectRoute) {

        $headers = self::getRequestHeaders();

        if ( array_key_exists("C-Conditional-Route", $headers) ) {

            $ObjectRoute
                ->setClass("test_route_second")
                ->setTarget("vendor/comodojo/dispatcher.servicebundle.test/services/test_route_second.php");

        }

        return $ObjectRoute;

    }

Router-side attributes inject
*****************************

TBW
