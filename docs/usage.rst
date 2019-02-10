.. _usage:

Basic usage
===========

.. _comodojo/dispatcher: https://github.com/comodojo/dispatcher
.. _comodojo/comodojo-installer: https://github.com/comodojo/comodojo-installer

The easiest way to use the dispatcher framework is installing it from the `comodojo/dispatcher`_ project package. In this case, the framework is almost ready to use: services and plugins have dedicated folders (pre-configured for autoloading), the main configuration file is created automatically and rewrite rules are in place (only for apache web server).

Alternatively, dispatcher can be integrated in a custom project simply installing it as a library. In this case, there are few steps to follow in order to start using it (see).

The dispatcher project package
------------------------------

.. note:: This section assumes that you have installed dispatcher using the dispatcher project package. If you are using dispatcher as a library in your custom project, skip this section and continue with :ref:`usage-library`.

The `comodojo/dispatcher`_ is the default project package for the dispatcher framework. It includes a standard folder structure, a default set of CLI command and the `comodojo/comodojo-installer`_ package.

Once installed, it creates the following folder structure:

::
    [base folder]/
        /cache
        /commands
        /config
        /logs
        /plugins
        /public
        /services

.. topic:: The document root

    The **public** folder is the document root directory. It contains the *index.php* file to start the framework and the *.htaccess* file to configure the rewrite rules in Apache server.

    To start dispatcher, **configure the document root of your server to this location**.

.. topic:: Configuration files

    Configuration files are in the **config** directory, in YAML format:

    - *comodojo-configuration.yml*, that contains the global configuration (e.g. cache, logs, ...)
    - *comodojo-routes.yml*, the initial configuration of the routing table
    - *comodojo-plugins.yml*, plugins configuration
    - *comodojo-commands.yml*, commands configuration

    More information on the configuration files in the :ref:config section.

.. topic:: Services, plugins and commands

    The three folders **services**, **plugins** and **commands** are there to host your custom services, plugins and commands, respectively. Since these folders are included in the autoloader, the custom classes have to be implemented using the declared class path: ``\Comodojo\Dispatcher\Services\`` for services, ``\Comodojo\Dispatcher\Plugins\`` for plugins and ``\Comodojo\Dispatcher\Command\`` for commands.

.. topic:: Temp files

    THe **cache** and the **log** folder are there only to host temporary files for cache (i.e. in case of a file-based cache provider) and logs (i.e. in case of logging enabled). In production installation, however, it's suggested to configure the framework to put those files in the OS default directories (e.g. */tmp* for cache and */var/log* for log files).

Your first hello world application
----------------------------------

To create your first hello world application using dispatcher, there are essentially two steps to follow:

1. create the hello world service;
2. configure the route to the service.

HelloWorld service
..................

An example HelloWorld service that implements (only) the HTTP GET method can be implemented as:

.. code-block:: php
    :linenos:

    <?php namespace Comodojo\Dispatcher\Services;

    use \Comodojo\Dispatcher\Service\AbstractService;

    class HelloWorld extends AbstractService {

        // HelloWorld is available ONLY via HTTP GET
        // Other HTTP verbs are not supported and, if requested, the framework
        // returns a "501 Method Not Implemented" response
        public function get() {

            // Access the request and the query component
            $query = $this->getRequest()->getQuery();

            // Get the "to" attribute
            $to = $query->get('to');

            // return content based on the received attribute
            return empty($to) ? 'Hello Comodojo!' : "Hello $to!";

        }

    }

Save this file as *HelloWorld.php* in the *services* folder. Since this path is registered in the autoloader for the ``\Comodojo\Dispatcher\Services`` namespace, the class will become immediately auto-loadable.

For more information about services, jump to the :ref:`services` section.

Configure the route to the service
..................................

Now that a service is available, we have to install a new route in the dispatcher router. The service accepts only one optional parameter **to** and we can use a regex to validate this parameter. For example, we can allow only alphanumeric chars, underscore and spaces, in any combination. Our route will be:

+--------------+-----------------------------+
| Base Path    | Variable Path               |
+==============+=============================+
| helloworld/  | {"to":"^[a-zA-Z0-9_\\s]+$"} |
+--------------+-----------------------------+

This route can be installed adding one entry in the *comodojo-routes.yml* file:

.. code-block:: yaml
    :linenos:

    helloworld:
        type: ROUTE
        class: \Comodojo\Dispatcher\Services\HelloWorld
        parameters: {}
        route: 'helloworld/{"to":"^[a-zA-Z0-9_\\s]+$"}'

If the file does not exist, create it as *config/comodojo-routes.yml*.

For more information about routes, jump to the :ref:`router` section.

Adding a plugin
---------------

Now that our HelloWorld service is in place, we can add a plugin to modify the global behaviour of the framework if one or more conditions are met.

An example could be: if (i) a route is matched, (ii) the route leads to the **HelloWorld** service and (iii) the request contains **text/html** in the *Accept* header then (iv) change the content-type to **text/html** and (v) wrap the text into a *<h1>* tag.

The plugin code will be:

.. code-block:: php
    :linenos:

    <?php namespace Comodojo\Dispatcher\Plugins;

    use \League\Event\AbstractListener;
    use \League\Event\EventInterface;

    class HelloWorldPlugin extends AbstractListener {

        public function handle(EventInterface $event) {

            $request = $event->getRequest();
            $response = $event->getResponse();
            $route = $event->getRouter()->getRoute();

            if (
                // (i) a route is matched
                $route !== null &&
                // (ii) the route leads to the **HelloWorld** service
                $route->getClassName() === '\Comodojo\Dispatcher\Services\HelloWorld' &&
                // (iii) the request contains **text/html** in the *Accept* header
                strpos($request->getHeaders()->get('Accept'), 'text/html') !== false
            ) {
                $content = $response->getContent()->get();
                // (iv) change the content-type to **text/html**
                $response->getContent()->setType('text/html');
                // (v) wrap the text into a <h1> tag
                $response->getContent()->set("<h1>$content</h1>");
            }

        }

    }

For more information about plugins and events, jump to the :ref:`plugins` section.

.. _usage-library:

Using dispatcher in custom projects
-----------------------------------

When used as a library in a custom project, dispatcher cannot rely on the pre-defined loader and also on the automation offered by the `comodojo/comodojo-installer`_ package.

For the above mentioned reasons, there are some steps that are needed to make the framework work:

.. topic:: Define your own folder structure

    The dispatcher framework does not require any specific folder: dispatcher will work even if all the code is stored in a flat folder.

    However, a good practice could be to start cloning the `comodojo/dispatcher`_ package and then add, change or delete files or folders according to your needs.

    Of course, another good practice is to create a document folder that is isolated from the code, but this is still up to you.

    .. note:: The only limitation is the name of the loader file (see next sections), that is autoprocessed by the framework to understand the actual absolute URI.

.. topic:: Add dispatcher as a dependency in your *composer.json*

    To add the dispatcher framework as a dependency, follow the :ref:`install-library` section.

.. topic:: Create the configuration

.. topic:: Writing the loader (*index.php* file)

    The *index.php* is the actual entry point to the framework, and its name is is the only limitation imposed by dispatcher.

    This file is in charge of:

    - creating an instance of dispatcher;
    - adding plugins and routes;
    - triggering the ``Dispatcher::dispatch()`` method.

    Creating a new instance of dispatcher is trivial:

    .. code-block:: php
        :linenos:

        use \Comodojo\Dispatcher\Dispatcher;
        $dispatcher = new \Comodojo\Dispatcher\Dispatcher([
            // configuration parameters here!
        ]);

    The class constructor supports optional parameter for:
    - events manager (An instance of the `\Comodojo\Foundation\Events\Manager <https://github.com/comodojo/foundation/blob/master/src/Comodojo/Foundation/Events/Manager.php>`_ class);
    - cache manager/provider (PSR-16 compliant);
    - logger (PSR-1 compliant).

    If no optional parameter is specified, dispatcher will create default (empty) object for you.

    Plugin can be installed using the EventManager:

    .. code-block:: php
        :linenos:

        $manager =  $dispatcher->getEvents();

    Routes have to be pushed in the routing table:

    .. code-block:: php
        :linenos:

        $table =  $dispatcher->getRouter()->getTable();

    If no routes are provided, by default dispatcher will reply a *404 - Not found* error to all requests.

    .. note:: For more information about routes, see the :ref:`router` section.

.. topic:: Rewrite rules

    Dispatcher relies on rewrite rules to work correctly. The rewrite routes shall be placed in the document folder at the same level of the *index.php* loader.

    An example rewrite rule (the one that the `comodojo/dispatcher`_ package uses by default) is the following for apache:

    .. code::

        <IfModule mod_rewrite.c>

            <IfModule mod_negotiation.c>
                Options -MultiViews
            </IfModule>

            Options +FollowSymLinks
            IndexIgnore */*

            RewriteEngine On

            RewriteCond %{REQUEST_FILENAME} !-f
            RewriteCond %{REQUEST_FILENAME} !-d

            RewriteRule (.*) index.php [L]
        </IfModule>

    Or the equivalent version for nginx could be:

    .. code::

        location / {
          if (!-e $request_filename){
            rewrite ^(.*)$ /index.php break;
          }
        }


.. topic:: Configure the web server
