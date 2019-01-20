.. _router:

Routing requests
================

.. _regular expressions: https://en.wikipedia.org/wiki/Regular_expression
.. _comodojo-installer: https://github.com/comodojo/comodojo-installer
.. _comodojo/dispatcher: https://github.com/comodojo/dispatcher

The dispatcher framework includes an advanced URL router that maps urls to services and allows users to describe routes using regular expressions, evaluate and convert url paths into parameters.

.. warning:: To understand how the dispatcher router works, a little knowledge of `regular expressions`_ is required.

Whenever an Http request is received by the Dispatcher, a ``\Comodojo\Dispatcher\Request\Model`` object is created and hydrated with all the informations inside about the original request. This includes also:

- uri object representation;
- http method;
- attributes and parameters;
- headers;
- user agent.

.. note:: For more information about the request model, see :ref:`request` section.

Once ready, this object is used by the router to find the correct route to the requested service.

If there isn't any route that match with the request, a ``\Comodojo\Exception\DispatcherException`` is thrown, catched and forwarded to the output processor to create an HTTP (not found) response accordingly.

.. note:: For more information about the output processor, see the :ref:`response-output-processor` section.

.. _router-route-anatomy:

Anatomy of a route
------------------

All the routes that dispatcher can support are composed by 3 different parts:

- the base path;
- the variable path;
- the query string.

Except for the least, these are used to identify a service which is eventually invoked by the framework and initialized with the parameters extracted from the URI and the data (i.e. POST data).

.. note:: The HTTP schema and the FQDN (location) parts of the URI are not examined by the router, but can be accessed from the ``\Comodojo\Dispatcher\Request\Model`` object.

To represent the route, dispatcher uses a combination of JSON structures and regex expressions inside a route string.

Let's see an example of how routes can be defined.

.. code-block:: javascript

    routes/test/{"page": "p(\\d+)"}/{"ux_timestamp*": "\\d{10}", "microseconds": "\\d{4}"}/{"filename*": "\\S+", "format*": "\\.(jpg|gif|jpeg|png)"}

We can split the route in two parts:

+--------------+--------------------------------------------------------------------------------------------------------------------------------------+
| Base Path    | Variable Path                                                                                                                        |
+==============+======================================================================================================================================+
| routes/test/ | {"page": "p(\\d+)"}/{"ux_timestamp*": "\\d{10}", "microseconds": "\\d{4}"}/{"filename*": "\\S+", "format*": "\\.(jpg|gif|jpeg|png)"} |
+--------------+--------------------------------------------------------------------------------------------------------------------------------------+

The **routes/test** is the base path and is used to uniquely identify the service to invoke. You can add as many paths as you need: this is meant to be used to build a sort of hierarchical structure among the routes. For example, if you're building a framework which provides REST APIs, you can create routes with basic paths like "api/v2/users/add" or "api/v3/products/remove" and so on. This part is static and it must be presented at the beginning of the HTTP request (right after the http-schema+fqdn).

The variable path is instead defined by json strings which provide an association between parameter names and regular expressions used to identify them. Mandatory fields are marked with an asterisk "*" at the end; if a field neither mandatory nor presented to the router, it will be skipped and therefore it will not be available to the service.

The following urls can be intercepted by route above:

- routes/test/p15/1467727094/image.jpg
- routes/test/p4/14677270941234/test-case.png
- routes/test/1467727094/smile.gif?user=test

Attributes and Parameters
-------------------------

Attributes and parameters (including the querystring) are automatically processed by the router and placed into the ``\Comodojo\Dispatcher\Request\Model`` object. From the event or service perspective, these parameters can be accessed using the ``Request::getQuery()`` method.

Refferring to the previous example:

- The first parameter is called 'page' and, because it doesn't end with an asterisk, it's not required and can be omitted. When it's used, it must start with a 'p' followed by at least one number.

- The path that follows is composed by two different parameters, one of which (ux_timestamp) is required. This means that it must be part of the HTTP request and it have to be made of 10 digits. The second parameter tells you that you can add another 4 digits which will be accessible as "microseconds".

- The last path is similar to the previous, except that both parameters are required (they both end with an astersk).

The request urls shown in the previous chapter will call the service associated with the route "routes/test" which will receive the following parameters (represented here like a PHP array):

- *routes/test/p15/1467727094/image.jpg*

.. code-block:: php

    [
        "page" => array('p15', '15'),
        "ux_timestamp" => '1467727094',
        "filename" => 'image',
        "format" => array('.jpg', 'jpg')
    ]

- *routes/test/p4/14677270941234/test-case.png*

.. code-block:: php

    [
        "page" => array('p4', '4'),
        "ux_timestamp" => '1467727094',
        "microseconds" => '1234',
        "filename" => 'test-case',
        "format" => array('.png', 'png')
    ]

- *routes/test/1467727094/smile.gif?user=test*

.. code-block:: php

    [
        "ux_timestamp" => '1467727094',
        "filename" => 'smile',
        "format" => array('.gif', 'gif'),
        "user" => 'test'
    ]

.. topic:: Handling back-references

    When a regular expression used in a route contains a back-reference, the parameter will be converted into an array where:
    - the first value is the full string;
    - the other values are the content of the back-references.

    So, while *{"page": "p(\\d+)"}* will lead to something like:

    .. code-block:: php

        [
            "page" => array('p4', '4')
        ]

    the same field (path) evaluated with *{"page": "p\\d+"}* will lead to something like:

    .. code-block:: php

        [
            "page" => 'p4'
        ]

Route definition
----------------

Every route can be defined using 4 different attributes:

- the route URL;
- the route type: *ROUTE*, *REDIRECT*, *ERROR* (see next sections);
- the class of the service to invoke (required only for *ROUTE* routes), in case of the endpoint of the route is a physical service;
- an array of parameters (optional), that can be used to configure optional - predefined - functionalities (e.g. route cache) or to extend them.

.. topic:: Route URL

    The route URL is the complete representation of a route, as specified in the :ref:`router-route-anatomy` section.

    Examples of valid routes are:

    - api
    - service/read
    - page/get/{"page": "\\d+"}
    - routes/test/{"page": "p(\\d+)"}/{"ux_timestamp*": "\\d{10}", "microseconds": "\\d{4}"}/{"filename*": "\\S+", "format*": "\\.(jpg|gif|jpeg|png)"}

.. topic: Route Types

    Dispatcher currently supports three types of routes:

    - **ROUTE**: physical route (i.e. a route that leads to a service);
    - **REDIRECT**: redirect-to-location route, used to perform a redirect whithout creating a dedicated service;
    - **ERROR**: redirect-to-error route, used to return an HTTP error whithout creating a dedicated service (e.g. generate 410 Gone response for a no longer available resource).

    Except for the first one, that relies completely on the underpinning service, the other types of routes can be customized using a dedicated set of parameters (see next sections).

.. topic:: Service Class

    This attribute defines the service that will be invoked by the router in case of a match. It has to be declared as a FQCN.

    The service itself, shall be a valid service (:ref:`services` section) that can be autoloaded.

.. topic:: Route parameters

    The last attribute can be used to provide an array of parameters for the route. There is no limitation on the name or the type of a parameter. However, some special parameters are used to configure internal dispatcher features.

    This pre-defined parameters are:

    - **redirect-code**: used in case of a *REDIRECT* route to change the specify the HTTP code. By default, dispatcher will use 302, 303 or 307, depending on the case.
    - **redirect-location**: the URL to redirect the client to.
    - **redirect-message**: the message to include in the redirect content
    - **redirect-type**: LOCATION (default) or REFRESH. The first uses HTTP redirect code to forward the client, the second creates a redirect page (200 OK Status Code) including the *Refresh* header and the redirect URI (in the page content).

    - **error-code**: in case of an *ERROR* route, the error code to be used (default 500).
    - **error-message**: the content of the HTTP error response (default *Internal Error*).

    - **cache**: the service caching strategy, *SERVER*, *CLIENT* or *BOTH* (see :ref:`services-cache` for more information).
    - **ttl**: the cache time to live, in seconds.

Route Installation
------------------

Routes can be installed in dispatcher in three different ways:

- programmatically;
- manually using a configuration file;
- automatically using the `comodojo-installer`_ package.

Add a route programmatically
............................

In order to install a new route programmatically, the access to the ``\Comodojo\Dispatcher\Dispatcher`` object is required *before* invoking the ``Dispatcher::dispatch()`` method. Once gained, the main class can be used to get the router instance and then its routing table.

.. code-block:: php

    $dispatcher = new \Comodojo\Dispatcher\Dispatcher();

    $router = $dispatcher->getRouter();

    $table = $router->getTable();

In the routing table there are two methods that allow the installation of the route(s).

.. topic:: ``Table::add()``

    The ``Table::add()`` method can be used to install a single route:

    .. code-block:: php

        $table->add(
            'routes/test/{"page": "p(\\d+)"}', // Route definition
            'ROUTE',                           // Route type
            '\\My\\Awesome\\Service',          // Service class
            [                                  // Parameters
                "cache" => "SERVER",
                "ttl"   => 3600
            ]
        );

    When you add a single route, this is volatile, it won't be stored in cache and the router won't remember it at the next startup.

.. topic:: ``Table::load()``

    This method is used to load one or multiple *permanent* route(s). The routes have to be passed as an array:

    .. code-block:: php

        $table->load(
            [
                "route" => 'routes/timestamp/{"ux_timestamp*": "\\d{10}", "microseconds": "\\d{4}"}',
                "type"  => 'ROUTE',
                "class" => '\\My\\Awesome\\TimestampService',
                "parameters" => []
            ],
            [
                "route" => 'routes/file/{"filename*": "\\S+", "format*": "\\.(jpg|gif|jpeg|png)',
                "type"  => 'ROUTE',
                "class" => '\\My\\Awesome\\FileService',
                "parameters" => []
            ]
        );

    The routes added with this method will be stored in cache and will be reloaded at the next startup.

.. note:: The ``Table::add()`` method is meant to be used by plugins, that can interact with the router in a case-by-case manner, without persisting the modifications on the routing table into the cache.

    ``Table::load()``, instead, is designed to load a bunch of routes once and permanently (at least for the routing-table-cache ttl), and so it'is mostly useful in the framework startup. The `comodojo/dispatcher`_ project package, for example, adopt the following strategy to evaluate the router status and, in case, load the routing table from file:

    .. code-block:: php

        if (
            file_exists($routes_file) &&
            empty($dispatcher->getRouter()->getTable()->getRoutes())
        ) {
            try {
                $routes = RoutesLoader::load($routes_file);
                $dispatcher->getRouter()->getTable()->load($routes);
            } catch (Exception $e) {
                http_response_code(500);
                exit("Unable to process routes, please check log: ".$e->getMessage());
            }
        }

Bypassing Router
----------------

There are some cases in which the request, after being evaluated, should pass through the router only if a specific condition is met. If not, the request has to be redirected to a specific service or location (for example, to redirect an unauthorized request to the login service/page). This is also called *pre-routing bypass*.

To bypass the router, it is possible to create a plugin that install a listener to a pre-routing event, like the following one:

.. code-block:: php

    <?php namespace My\Awesome;

    use \League\Event\AbstractListener;
    use \League\Event\EventInterface;

    class RedirectToLogin extends AbstractListener {

        public function handle(EventInterface $event) {

            if ( $this->requestHasToBeReRouted($this->getRequest()) === false ) {

                $router = $event->getRouter();

                $route = new \Comodojo\Dispatcher\Router\Route();

                $route->setClass("\\My\\Awesome\\LoginService")
                    ->setType("ROUTE");

                $router->bypassRouting($route);

            }

        }

        protected function requestHasToBeReRouted($request) {
            // some condition here //
        }

    }

    // a sample code to install the plugin
    // $dispatcher->getEvents()->subscribe('dispatcher.request.#', '\My\Awesome\RedirectToLogin');

Bypassing Service
-----------------

In some other cases, afer a route has been found, the service should run only if a specific condition is met. This is also called *post-routing bypass*.

To skip the service, it is possible to create a plugin that installs a listener to a post-routing event and uses the ``Router::bypassService()`` method, like the following one:

.. code-block:: php

    <?php namespace My\Awesome;

    use \League\Event\AbstractListener;
    use \League\Event\EventInterface;

    class BypassSpecialService extends AbstractListener {

        public function handle(EventInterface $event) {

            if ( $this->serviceHasToRun($this->getRequest()) === false ) {

                $response = $event->getResponse();

                $response->getContent()->set("This service requires a special authentication");
                $response->getStatus()->set(403);

                $router->bypassService();

            }

        }

        protected function serviceHasToRun($request) {
            // some condition here //
        }

    }

    // a sample code to install the plugin
    // $dispatcher->getEvents()->subscribe('dispatcher.route', '\My\Awesome\RedirectToLogin');
