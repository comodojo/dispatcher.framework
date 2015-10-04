The event system
================

Dispatcher has an integrated event's system that can be used to extend its features by plugins.

When a client calls dispatcher, request, route and response are modeled as objects and provided to the callback function hooked to relative event.

Dispatcher starts to emit events as soon as a request is received.

Let's consider an example::

    global $dispatcher;
    
    function custom_404($ObjectError) {
    
        $error_page = file_get_contents(DISPATCHER_REAL_PATH."vendor/comodojo/dispatcher.plugin.test/resources/html/404.html");
    
        $ObjectError->setContent($error_page);
    
        return $ObjectError;
    
    }
    
    $dispatcher->addHook("dispatcher.error.404", "custom_404");

In this example, a plugin will set a custom content in default 404 error page by:

- catching event "dispatcher.error.404";
- replacing error content using "setContent()" method;
- returning to framework the modified object.

How dispatcher emits events
***************************

By default, dispatcher uses this schema to emit events:

    [*framework*].[*event*].[*notification*|*marker*]

In practice:

- [framework] will always be populated by the string *dispatcher*;

- [event] represents the event macroclass; possible values are *request*, *routingtable*, *serviceroute*, *result*, *route*, *redirect* or *error*.

- [notification|marker] is a detailed view of what is happening; it can assume different values, like HTTP return codes (like **404** in the previous example for a "not found" response.

There are also two special category of events:

- start event (*dispatcher*), that will fire at framework startup;
- markers, fired to express a particular condition (like *#* that denote the end of specific event's macroclass).

Each type of event expects the callback to return a particular kind of object. If something different is provided, callback's result will be discarded.

.. note:: If a single event is hooked to multiple callbacks, it will behave as a chain: the first result (if any) will be the input of the second callback and so on.

Sone examples are:

- `dispatcher.serviceroute` - a level 2 event that expose the route retrieved for the current request

- `dispatcher.error.404` - a level 3 event for a not found response

- `dispatcher.result.#` - a level 3 event that fires after every other callback

The complete event list
***********************

- dispatcher receive a request

    - *dispatcher* - marks the frameworks has entered the running cycle and exposes the whole `Comodojo\Dispatcher\Dispatcher` instance without expecting any return value

- Request is modeled as an instance of `\Comodojo\Dispatcher\ObjectRequest\ObjectRequest`

    - *dispatcher.request* - provides and expects an instance of `\Comodojo\Dispatcher\ObjectRequest\ObjectRequest`

    - *dispatcher.request.[METHOD]* - provides and expects an instance of `\Comodojo\Dispatcher\ObjectRequest\ObjectRequest`

    - *dispatcher.request.[SERVICE]* - provides and expects an instance of `\Comodojo\Dispatcher\ObjectRequest\ObjectRequest`

    - *dispatcher.request.#* - provides a `\Comodojo\Dispatcher\ObjectRequest\ObjectRequest`, will fire after every other callback discarding returned data

- An instance of `\Comodojo\Dispatcher\ObjectRoutingTable\ObjectRoutingTable` is created

    - *dispatcher.routingtable* - provides and expects an instance of `\Comodojo\Dispatcher\ObjectRoutingTable\ObjectRoutingTable`

- A route was retrieved from routingtable

    - *dispatcher.serviceroute* - provides and expects an instance of `\Comodojo\Dispatcher\ObjectRoute\ObjectRoute`

    - *dispatcher.serviceroute.[TYPE]* - provides and expects an instance of `\Comodojo\Dispatcher\ObjectRoute\ObjectRoute`

    - *dispatcher.serviceroute.[SERVICE]* - provides and expects an instance of `\Comodojo\Dispatcher\ObjectRoute\ObjectRoute`

    - *dispatcher.serviceroute.#* - provides a `\Comodojo\Dispatcher\ObjectRoute\ObjectRoute`, will fire after every other callback and discarding returned data

- Once a route is retrieved, dispatcher will run a service, redirect to another location or return an error; in any case, a result object that implements the `\Comodojo\Dispatcher\ObjectResult\ObjectResultInterface` is inited, provided to the callback and expected as result.

    - *dispatcher.result*

    - In case of success

        - *dispatcher.route*

        - *dispatcher.route.[STATUSCODE]*

    - In case of redirect

        - *dispatcher.redirect*

        - *dispatcher.redirect.[STATUSCODE]*

    - In case of error

        - *dispatcher.error*

        - *dispatcher.error.[STATUSCODE]*

    - *dispatcher.result.#* will fire after every other callback and expects an instance of `\Comodojo\Dispatcher\ObjectResult\ObjectResultInterface`

- result is returned to client
