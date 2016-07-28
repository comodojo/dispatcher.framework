Routing requests
================

.. _regular expressions: https://en.wikipedia.org/wiki/Regular_expression

Dispatcher includes an advanced embedded URL router that maps urls to services. It allows users to configure routes through Regular Expressions in order to convert url paths in parameters passed to the service.

All the routes can be composed by 3 different parts: the base paths, the variable parameter paths and finally the query string. Except for the least, these are used to identify a service which is eventually invoked by the framework and initialized with the parameters extracted from the url itself and from the query string (and the post data).

Let's see an example of how routes can be defined:

.. code-block:: javascript

    routes/test/{"page": "p(\\d+)"}/{"ux_timestamp*": "\\d{10}", "microseconds": "\\d{4}"}/{"filename*": "\\S+", "format*": "\\.(jpg|gif|jpeg|png)"}

In the above example, the "routes/test" is the base path, you can add as many paths as you need. This is meant to be used to build a sort of hierarchical structure among the routes. For example, if you're building a framework which provides API REST, you can create routes with basic paths like "api/v2/users/add" or "api/v3/products/remove" and so on. This part is static and it must be present at the beginning of the HTTP request ight after the installation path of the framework.

The variable part is instead defined by json strings which provide an association between parameter names and regular expressions used to identify them. Unless there's at least a parameter whose name ends with an asterisk "*", the paths can be omitted and therefor those parameters won't be available to the service.

The following urls can be intercepted by route above:

- routes/test/p15/1467727094/image.jpg
- routes/test/p4/14677270941234/test-case.png
- routes/test/1467727094/smile.gif?user=test

Attributes and Parameters
*************************

To understand how it works, a little knowledge of `regular expressions`_ is required.

As you can see in the example above, the first parameter is called 'page' and, because it doesn't end with an asterisk, it's not required and can be omitted, when it's used, it must start with a 'p' followed by at least one number.

The following path is composed by two different parameters, one of which (ux_timestamp) is required. This means that it must be part of the HTTP request and it have to be made of 10 digits, the second parameter tells you that you can add another 4 digits which will be accessible as "microseconds".

The last path is similar to the previous, except that both parameters are required (they both end with an astersk).

The request urls shown in the previous chapter will call the service associated with the route "routes/test" which will receive the following parameters:

- routes/test/p15/1467727094/image.jpg

.. code-block:: php

    "page" => array('p15', '15')
    "ux_timestamp" => '1467727094'
    "filename" => 'image'
    "format" => array('.jpg', 'jpg')

- routes/test/p4/14677270941234/test-case.png

.. code-block:: php

    "page" => array('p4', '4')
    "ux_timestamp" => '1467727094'
    "microseconds" => '1234'
    "filename" => 'test-case'
    "format" => array('.png', 'png')

- routes/test/1467727094/smile.gif?user=test

.. code-block:: php

    "ux_timestamp" => '1467727094'
    "filename" => 'smile'
    "format" => array('.gif', 'gif')
    "user" => 'test'

When a regular expression contains a back-reference, the parameter will be an array where the first value is the full string while the other values are the content of the back-references.

Defining routes
***************

In order to create routes, you need to access the dispatcher router as follows:

.. code-block:: php

    $dispatcher = new \Comodojo\Dispatcher\Dispatcher();
    $router = $dispatcher->router();

Once you gain access to the router, there are two ways to add routes. You can either use the *add()* method of the routing table, or load a configuration array with a series of routes.

Every route can be defined by 4 different parameters:

- the route URL,
- the route type,
- the class of the object to load,
- a list of parameters.

If you want to add a single route, you can do it as follows:

.. code-block:: php

    $router->table()->add(
        'routes/test/{"page": "p(\\d+)"}', // Route definition
        'ROUTE',                           // Route type
        '\\My\\Awesome\\Service',          // Service class
        array(                             // Parameters
            "cache" => "SERVER",
            "ttl"   => 3600
        )
    );
    
When you add a single route, this is volatile, it won't be stored in cache and the router won't remember it at the next startup.

If you want to add different routes at once, you can do it as follows:

.. code-block:: php

    $router->table()->load(
        array(
            "route" => 'routes/timestamp/{"ux_timestamp*": "\\d{10}", "microseconds": "\\d{4}"}',
            "type"  => 'ROUTE',
            "class" => '\\My\\Awesome\\TimestampService',
            "parameters" => array()
        ),
        array(
            "route" => 'routes/file/{"filename*": "\\S+", "format*": "\\.(jpg|gif|jpeg|png)',
            "type"  => 'ROUTE',
            "class" => '\\My\\Awesome\\FileService',
            "parameters" => array()
        )
    );

The routes added with this method will be stored in cache and will be reloaded at the next startup.

Routing a request
*****************

Whenever a url request is received by the *Dispatcher*, a *Request* object is created with all the informations inside (like *GET* or *POST* parameters, http headers and so on).

This object can be used to find the correct route to the requested service.

.. code-block:: php

    $router->route($requestObject);  // \Comodojo\Dispatcher\Request\Model $requestObject
    
Once the request is elaborated, you can access the route as follows:

.. code-block:: php

    $route = $router->getRoute();
    
    echo $route->getServiceName();
    
If there isn't any route that match with the request, a DispatcherException is rised and the *getRoute()* method will return *null*.

Composing a response
********************

Once the request is routed to an actual service, it is possible to compose a Response object. The router itself will execute the service and provide the resulting output to the Response objcet.

.. code-block:: php

    $response = new \Comodojo\Dispatcher\Response\Model(
        $router->configuration(), 
        $router->logger()
    );
    
    $router->compose($response);
    
    echo $response->content()->get();

Bypassing Router
****************

If you want to bypass the router (for example, to redirect an unauthorized request to the login service) you can build a plugin in order to cacth a pre-routing event.

.. code-block:: php

    function pluginListener($args) {
    
        $event = $args[0];
        
        $router = $event->dispatcher()->router();
        
        $route = new \Comodojo\Dispatcher\Router\Route();
        
        $route->setClass("\\My\\Awesome\\LoginService")
            ->setType("ROUTE");
            
        $router->bypassRouting($route);
    
    }
    
    $dispatcher->events()->subscribe('dispatcher.request.#', 'pluginListener');


Bypassing Service
*****************

You can also completely avoid the routing process and return a predefined response (for example, if you cached a result and you want to use the saved data instead of the live one).

.. code-block:: php

    function pluginListener($args) {
    
        $event = $args[0];
        
        $dispatcher = $event->dispatcher();
        
        $dispatcher->response()->set("My awesome response!");
        
        $dispatcher->router()->bypassService();
    
    }
    
    $dispatcher->events()->subscribe('dispatcher.request.#', 'pluginListener');
