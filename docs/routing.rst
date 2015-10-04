Routing requests
================

Dispatcher has an embedded URL router that maps urls to services, using an approach slightly different from the one used in other common framework.

Instead of map methods (or functions) to full uris (plus HTTP verbs), dispatcher retrieves routes using only the first path in the URI. In practice, this variable *select* the route the request will follow.

This kind of next-hop approach leaves all logic to the service itself (for example, the ability to serve POST requests instead of GET), decoupling routing and service processing.

.. note:: To understand how to handle HTTP methods jump to the service section.

Working modes
*************

Attributes and Parameters
*************************

Defining routes
***************

To define a new route, the `setRoute()` method should be invoked before the `dispatch()`.

Syntax of method is::

    setRoute( [route], [type], [target], [parameters], [relative] )

...

So, an example route could be::

    $dispatcher->setRoute("helloworld", "ROUTE", "HelloWorld.php", array(), true);

Predefined routes
*****************

Autorouting
***********

Conditional routing
*******************

Router-side attributes inject
*****************************
