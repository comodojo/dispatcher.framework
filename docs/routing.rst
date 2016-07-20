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

TBC

Defining routes
***************

In order to create routes, you need to access the dispatcher router as follows:

.. code-block:: php

    $dispatcher = new \Comodojo\Dispatcher\Dispatcher();
    $router = $dispatcher->router();

Once you gain access to the router, there are two ways to add routes. You can either use the *add* method of the routing table, or load a configuration array with a series of routes.

Every route can be defined by 4 different parameters:

- the route URL,
- the route type,
- the class of the object to load,
- a list of parameters.


Bypass Router
*************

TBW
