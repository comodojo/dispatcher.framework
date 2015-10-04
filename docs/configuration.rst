Configuration
=============

.. _dispatcher.project: https://github.com/comodojo/dispatcher.project
.. _dispatcher.framework: https://github.com/comodojo/dispatcher.framework
.. _psr-3: http://www.php-fig.org/psr/psr-3/

This section covers the framework's configuration if installed via `dispatcher.project`_ package.

If you are using `dispatcher.framework`_ as a library, you should define constants, directives and folders manually.

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

Configuration files
*******************

Dispatcher comes out of the box with a default configuration that can be edited to change global behaviour of framework.

Configuration files are contained in *configs* folder:

- *dispatcher-config.php*: contains constants defined to change some aspect of framework (like paths, logging, ...)
- *plugins-config.php*: (should) contain plugins init scripts; initially empty
- *routing-config.php*: (should) contain declaration of routes; initially empty

These files are loaded at boot time.

General properties
******************

DISPATCHER_REAL_PATH
""""""""""""""""""""
Dispatcher real path.

.. code-block:: php

    define("DISPATCHER_REAL_PATH",realpath(dirname(__FILE__))."/../");

DISPATCHER_BASEURL
""""""""""""""""""
Dispatcher baseurl. If not defined, dispatcher will try to resolve absolute base url itself (default).

.. code-block:: php

    define("DISPATCHER_BASEURL","");

DISPATCHER_ENABLED
""""""""""""""""""
If false, dispatcher will not route any request and will reply with an *503 Service Temporarily Unavailable* status code.

.. code-block:: php

    define ('DISPATCHER_ENABLED', true);
    
DISPATCHER_USE_REWRITE
""""""""""""""""""""""
If true, dispatcher will use rewrite module to acquire service path and attibutes.

If you prefer to turn this feature off, remember to remove/rename .htaccess file in installation's folder and/or disable the apache rewrite module.

.. code-block:: php

    define ('DISPATCHER_USE_REWRITE', true);

DISPATCHER_AUTO_ROUTE
"""""""""""""""""""""
Enable/disable the autoroute function; if true, dispatcher will try to route requests to not declared services using filenames.

.. code-block:: php

    define('DISPATCHER_AUTO_ROUTE', false);

DISPATCHER_DEFAULT_ENCODING
"""""""""""""""""""""""""""
Sets the system-wide default encoding.

.. code-block:: php

    define('DISPATCHER_DEFAULT_ENCODING', 'UTF-8');

DISPATCHER_SUPPORTED_METHODS
""""""""""""""""""""""""""""
HTTP supported methods.

This represent the pool of framework-supported HTTP methods, but each service can implement one or more methods independently. This value may change the *Allow Response* Header in case of 405 response.

Change this value only if:
- you need to support other http methods (like PUSH)
- you want to disable globally a subset of HTTP methods (i.e. if you want to disable PUT requests globally, you can omit it from this definition; method will be ignored even though service implements it - or implements the *ANY* wildcard).

.. note:: a service that not implements one of this methods, in case of unsupported method request, will reply with a *501-not-implemented* response; this behaviour is managed automatically.

.. warning:: this constant should be in plain, uppercased, comma separated, not spaced text.

.. warning:: DO NOT USE a "ANY" method here or it will override the embedded wildcard ANY.

.. code-block:: php

    define('DISPATCHER_SUPPORTED_METHODS', 'GET,PUT,POST,DELETE');

Logging
*******

DISPATCHER_LOG_ENABLED
""""""""""""""""""""""
enable/disable logger (monolog).

.. code-block:: php

    define('DISPATCHER_LOG_ENABLED', false);

DISPATCHER_LOG_NAME
"""""""""""""""""""
Log channel name.

.. code-block:: php

    define('DISPATCHER_LOG_NAME', 'dispatcher');

DISPATCHER_LOG_TARGET
"""""""""""""""""""""
Log target (file or *null* for error_log).

.. code-block:: php

    define('DISPATCHER_LOG_TARGET', null)

DISPATCHER_LOG_LEVEL
""""""""""""""""""""
Debug level, as in `psr-3`_ standard.

.. code-block:: php

    define('DISPATCHER_LOG_LEVEL', 'ERROR')
    
Folders
*******

DISPATCHER_CACHE_FOLDER
"""""""""""""""""""""""
Cache folder.

.. code-block:: php

    define('DISPATCHER_CACHE_FOLDER', DISPATCHER_REAL_PATH."cache/");

DISPATCHER_SERVICES_FOLDER
""""""""""""""""""""""""""
Services folder.

.. code-block:: php

    define('DISPATCHER_SERVICES_FOLDER', DISPATCHER_REAL_PATH."services/");

DISPATCHER_PLUGINS_FOLDER
"""""""""""""""""""""""""
Plugins folder.

.. code-block:: php

    define('DISPATCHER_PLUGINS_FOLDER', DISPATCHER_REAL_PATH."plugins/");

DISPATCHER_TEMPLATES_FOLDER
"""""""""""""""""""""""""""
Templates folder.

.. code-block:: php

    define('DISPATCHER_TEMPLATES_FOLDER', DISPATCHER_REAL_PATH."templates/");

DISPATCHER_LOG_FOLDER
"""""""""""""""""""""
Logs folder.

.. code-block:: php

    define('DISPATCHER_LOG_FOLDER', DISPATCHER_REAL_PATH."logs/");

Cache
*****

DISPATCHER_CACHE_ENABLED
""""""""""""""""""""""""
Enable/disable cache support.

.. code-block:: php

    define('DISPATCHER_CACHE_ENABLED', true);

DISPATCHER_CACHE_DEFAULT_TTL
""""""""""""""""""""""""""""
Default cache time to live, in seconds.

.. code-block:: php

    define('DISPATCHER_CACHE_DEFAULT_TTL', 600);

DISPATCHER_CACHE_FAIL_SILENTLY
""""""""""""""""""""""""""""""
If true, cache will fail silently in case of error without throwing exception.

.. code-block:: php

    define('DISPATCHER_CACHE_FAIL_SILENTLY', true);
