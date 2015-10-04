Comodojo dispatcher docs
========================

.. image:: assets/dispatcher_logo.png

Comodojo dispatcher is a service-oriented REST microframework designed to be simple to use, extensible and fast.

Before version 3 project name was "SimpleDataRestDispatcher" and it was mainly oriented to JSON and XML pure-data REST services. Since version 3.0, dispatcher has become a totally different project, designed to publish and manage (almost) any kind of data.

Dispatcher is structured on top of following main concepts:

- A dispatcher instance is a sort of multi-service container; services are grouped in bundles, managed by composer and installed from the project package. Installing or removing a bundle should never stop or interfere with other bundles or the dispatcher itself.

- Services are the central point of framework's logic: they are independent, callable php classes that may return data (in any form) or errors (exception); a service must extend the `\Comodojo\Dispatcher\Service` class.

- Routes are paths (urls) that may be associated to a service, redirect to another location or generate errors (exception); in practice, routes are only paths that forward a request to a service without any knowledge of the service's logic.

- Services and routes are completely separated. It means that a single service may be reached via multiple routes and it's life does not depend on any of them.

- In dispatcher, (almost) everything about requests, routes and results can be modified using events' subsystem; plugins are made to hook those events and interact with the framework. They can also be packed in bundles and managed using composer.

Contents:

.. toctree::
   :maxdepth: 2

   installation
   configuration
   howitworks
   services
   routing
   events
   plugins
