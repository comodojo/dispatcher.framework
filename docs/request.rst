.. _requests:

The request model
=================

Dispatcher starts creating a request model as soon as a new instance is created (i.e. once a request hits the entry point). The model is then ready to be consumed by services, events and the framework itself.

.. note:: it is possible, although not suggested, to alter the request model during the instance lifecycle. Dispatcher will not prevent you from changing the model, but doing this keep in mind that this could alter the framework behavior.

Anatomy of a request
--------------------

The request model includes the following attributes:

- request time (received)
- request headers
- request URI
- actual query
- useragent specs
- POST data
- FILE data
- HTTP method
- HTTP version

Request time
............

The actual request time (i.e. when the request was received and dispatcher started its modeling) can be accessed using the ``Model::getTiming()`` (as float):

.. code-block:: php
    :linenos:

    $request = $dispatcher->getRequest();
    $time = $request->getTiming(); // e.g. 1549908679.109

Request headers
...............

To access request headers, the ``\Comodojo\Dispatcher\Request\Headers`` object can be accessed using the ``Model::getHeaders()`` method:

.. code-block:: php
    :linenos:

    $request = $dispatcher->getRequest();
    $headers = $request->getHeaders(); // returns an Headers object

Then, to get the headers:

.. code-block:: php
    :linenos:

    $host_header = $headers->get('Host'); // e.g. "example.com"

    $all_headers = $headers->get(); // returns an array of headers:
        // array (
        //     'Host' => 'example.com',
        //     'Connection' => 'keep-alive',
        //     'Cache-Control' => 'max-age=0',
        //     'Upgrade-Insecure-Requests' => '1',
        //     'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/72.0.3626.96 Safari/537.36',
        //     'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
        //     'Accept-Encoding' => 'gzip, deflate',
        //     'Accept-Language' => 'it-IT,it;q=0.9,en-US;q=0.8,en;q=0.7'
        // )

.. note:: For more information about header manipulation APIs, see `dispatcher APIs documentation <https://api.comodojo.org/dispatcher/master/Comodojo/Dispatcher/Request/Headers.html>`_

Request URI
...........

TBW

Request query
.............

TBW

User Agent
..........

The get the client User Agent, the ``\Comodojo\Dispatcher\Request\UserAgent`` object can be accessed using the ``Model::getUserAgent()`` method:

.. code-block:: php
    :linenos:

    $request = $dispatcher->getRequest();
    $ua = $request->getUserAgent(); // returns a UserAgent object

    $ua_string = (string) $ua; // equivalent to $ua->get()

.. note:: If the *browscap.ini* database is enabled in your php.ini config, the ``UserAgent::browser()`` methods returns parsed information of current browser.

POST data
.........

TBW

FILE data
.........

TBW

HTTP method
...........

The current HTTP verb can be found calling the ``Model::getMethod()`` method:

.. code-block:: php
    :linenos:

    $request = $dispatcher->getRequest();
    $method = $request->getMethod(); // returns a Method object

    $http_method = (string) $method; // equivalent to $method->get()

HTTP version
............

The current HTTP protocol version can be found calling the ``Model::getVersion()`` method:

.. code-block:: php
    :linenos:

    $request = $dispatcher->getRequest();
    $version = $request->getVersion(); // returns a Version object

    $http_version = (string) $version; // equivalent to $version->get()
