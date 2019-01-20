.. _usage:

Basic usage
===========

.. _comodojo/dispatcher: https://github.com/comodojo/dispatcher

The easiest way to use the dispatcher framework is installing it from the `comodojo/dispatcher`_ project package. In this case, the framework is almost ready to use: services and plugins have dedicated folders (pre-configured for autoloading), the main configuration file is created automatically and rewrite rules are in place (only for apache web server).

Alternatively, dispatcher can be integrated in a custom project simply installing it as a library. In this case, there are few steps to follow in order to start using it (see).

The dispatcher project package
------------------------------

.. note:: This section assumes that you have installed dispatcher using the dispatcher `project package`_. If you are using dispatcher as a library in your custom project, skip this section and continue with :ref:`usage-library`.

The `comodojo/dispatcher`_ is the default project package for the dispatcher framework. It includes a standard folder structure, a default set of CLI command and the `comodojo/comodojo-installer`_ package.

Once installed, it creates the following folder structure:

[root folder]/
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

An example could be: if (i) the service is the **HelloWorld** and (ii) the request contains **text/html** in the *Accept* header, then (iii) change the content-type to **text/html** and (iv) wrap the text into a *<h1>* tag.

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

            // (i) the service is the HelloWorld
            $is_html = strpos($request->getHeaders()->get('Accept'), 'text/html') !== false;

            // (ii) the request contains text/html in the Accept header
            $is_hw = $event->getRouter()->getRoute()->getClassName() === '\Comodojo\Dispatcher\Services\HelloWorld';

            // IF (i) AND (ii)
            if ( $is_html && $is_hw ) {

                $content = $response->getContent()->get();

                // (iii) change the content-type to **text/html**
                $response->getContent()->setType('text/html');

                // (iv) wrap the text into a <h1> tag
                $response->getContent()->set("<h1>$content</h1>");

            }

        }

    }

For more information about plugins and events, jump to the :ref:`plugins` section.

.. _usage-library:

Using dispatcher in custom projects
-----------------------------------

The index file
..............

Rewrite rules
.............

Dispatcher relies on rewrite rules to work correctly.

An example rewrite rule (included by default in the `dispatcher project package`_) is the following for apache:

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

Or the equivalent version for nginx:

.. code::

    location / {
      if (!-e $request_filename){
        rewrite ^(.*)$ /index.php break;
      }
    }
