Configuration
=============

.. _comodojo/dispatcher: https://github.com/comodojo/dispatcher

The dispatcher framework can be configured passing configuration statements as an associative array when creating a new ``\Comodojo\Dispatcher\Dispatcher`` instance.

.. code-block:: php
    :linenos:

    use \Comodojo\Dispatcher\Dispatcher;
    $dispatcher = new \Comodojo\Dispatcher\Dispatcher([
        // configuration parameters here!
    ]);

A init time, the configuration is used to create basic objects (e.g. models, cache provider) and can be changed at any time accessing the configuration object (that will be an instance of `\Comodojo\Foundation\Basic\Configuration <https://github.com/comodojo/foundation/blob/master/src/Comodojo/Foundation/Basic/Configuration.php>`_ class).

.. code-block:: php
    :linenos:

    // get the configuration object
    $configuration = $dispatcher->getConfiguration();

    // get the base-path value from actual configuration
    $base_path = $configuration->get('base-path');

    // set the my-param configuration item using standard notation
    $configuration->set('my-param', 'foo');

    // set the my->remote->url nested configuration item using dot notation
    $configuration->set('my.remote.url', 'https://example.com')

.. note:: For more information about the ``\Comodojo\Foundation\Base\Configuration`` class, see the `comodojo/foundation documentation <https://docs.comodojo.org/projects/foundation/en/latest/base.html>`_

Configuration parameters
------------------------

Following the list of configuration statement currently supported by the framework.

.. topic:: (bool) enabled [default: true]

    Enable or disable the framework (i.e. if not enabled, dispatcher cannot serve any request).

.. topic:: (int) disabled-status [default: 503]

    In case not enabled, the HTTP status to return to clients.

.. topic:: (string) disabled-message [default: Dispatcher offline]

    In case not enabled, the HTTP body to return to clients.

.. topic:: (string) encoding [default: UTF-8]

    Active char encoding.

.. topic:: (bool) routing-table-cache [default: true]

    Enable or disable the caching of the routing table.

.. topic:: (int) routing-table-ttl [default: 86400]

    The ttl for the routing table cache.

.. topic:: (array) supported-http-methods (default: null)

    This setting could be used to restring (or enhance) the support for HTTP methods.

    If not defined, the framework will allow the following http verbs:

    - GET
    - PUT
    - POST
    - DELETE
    - OPTIONS
    - HEAD
    - TRACE
    - CONNECT
    - PURGE

.. topic:: (array) log

    Logger configuration, input for the ``LogManager::createFromConfiguration()`` method (for more information, see the `comodojo/foundation <https://docs.comodojo.org/projects/foundation/en/latest/logging.html>`_ documentation)

    An example schema:

    .. code-block:: yaml
        :linenos:

        log:
            enable: true
            name: dispatcher
            providers:
                local:
                    type: StreamHandler
                    level: debug
                    stream: logs/dispatcher.log

.. topic:: (array) cache

    Cache manager of provider configuration, input for the ``SimpleCacheManager::createFromConfiguration()`` method (for more information, see the `comodojo/cache <https://docs.comodojo.org/projects/cache/en/latest/index.html>`_ documentation)

    An example schema:

    .. code-block:: yaml
        :linenos:

        cache:
            enable: true
            pick_mode: PICK_FIRST
            providers:
                local:
                    type: Filesystem
                    cache_folder: cache

Automatic configuration parameters
----------------------------------

The following (basic) configuration parameters are computed and included in the configuration at init time.

.. note:: The user configuration has precedence over the automatic one: if one automatic parameter is included in the user configuration, its value will overwrite the automatic one.

.. topic:: (string) base-path

    The base path of the project directory (i.e. the root of the *vendor* folder).

.. topic:: (string) base-url

    The current URL used to contact the framework.

.. topic:: (string) base-uri

    The full URI used to contact the framework.

.. topic:: (string) base-location

    The relative path before the entry point (i.e. the *index.php* file).
