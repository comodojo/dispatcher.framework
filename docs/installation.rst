Installation
============

.. _dispatcher project package: https://github.com/comodojo/dispatcher
.. _composer: https://getcomposer.org/

The comodojo dispatcher framework can be installed using `composer`_ as a product (using the dedicated `dispatcher project package`_) or as a library.

Install dispatcher as a product
-------------------------------

To install dispatcher as a product, simply run the composer create-project command (assuming *dispatcher* as your project folder):

.. code:: bash

    composer create-project comodojo/dispatcher dispatcher

Composer will install the dependencies and create the main configuration file. Once completed, configure the web server to use *dispatcher/public* as the document root.

Install dispatcher as a library
-------------------------------

To install dispatcher as a library in your own project:

.. code:: bash

    composer require comodojo/dispatcher.framework

.. note:: If installed as a standalone library, no automatic configuration will be performed by composer. To create a custom project based on dispatcher is highly recommended to start cloning and changing the `dispatcher project package`_.

Requirements
------------

To work properly, dispatcher requires a web server and PHP >= 5.6.0.
