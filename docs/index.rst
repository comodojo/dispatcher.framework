Comodojo dispatcher docs
========================

.. image:: assets/dispatcher_logo.png

.. note:: You're reading documentation of development release; content may change without notice following framework development.

Comodojo dispatcher is a service-oriented, hackable REST microframework designed to be simple and fast.

Before version 3 project name was "SimpleDataRestDispatcher" and it was mainly oriented to JSON and XML pure-data REST services. Since version 3.0, dispatcher has become a totally different project, designed to publish and manage (almost) any kind of data.

Dispatcher is structured on top of following main concepts:

- A dispatcher instance is a sort of multi-service container; services are grouped in bundles, managed by composer and installed from the project package. Installing or removing a bundle should never stop or interfere with other bundles or the dispatcher itself.

- Services are the central point of framework's logic: they are independent, callable php classes that may return data (in any form) or errors (exception); a service must extend the `\Comodojo\Dispatcher\Service` class.

- Routes are paths (urls) that may be associated to a service, redirect to another location or generate errors (exception); in practice, routes are only paths that forward a request to a service without any knowledge of the service's logic.

- Services and routes are completely separated. It means that a single service may be reached via multiple routes and it's life does not depend on any of them.

- In dispatcher, (almost) everything about requests, routes and results can be modified using events' subsystem; plugins are made to hook those events and interact with the framework. They can also be packed in bundles and managed using composer.

Directory structure
*******************

The project package will create the following directory structure (excluding files)::

    dispatcher/
        - DispatcherInstaller/ => contains the DispatcherInstaller class, fired when composer install/update/remove packages
        - cache/ => cache files will be written here; ensure apache user can write here
        - configs/ => configuration files' folder
        - logs/ => where monolog (if enabled) will write logs
        - plugins/ => this folder is dedicated to plugins manually installed or created by user
        - services/ => this folder is dedicated to services manually installed or created by user
        - templates/ => this folder is dedicated to templates manually installed or created by user
        - vendor/ => composer standard vendor folder
        - index.php
        - [...]
        

.. note:: It's important to understand that plugin/service/template packages will be installed in *vendor* folder, respecting the composer installation standard. This because mixing user files and package files could be a not optimal solution to handle updates or customization. The framework can address services and plugins using a relative/absolute path convention, to keep installer aware of where packages are located.

Contents:

.. toctree::
   :maxdepth: 2

   installation
   configuration
   services
   routing
   events
   plugins
