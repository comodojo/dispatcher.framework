Installation
============

.. _dispatcher project package: https://github.com/comodojo/dispatcher
.. _composer: https://getcomposer.org/

The comodojo dispatcher framework can be installed using `composer`_ as a product (using the dedicated `dispatcher project package`_) or as a library.

To install it as a product:

.. code:: bash

    composer create-project comodojo/dispatcher dispatcher

Or, to intall it as a library in your own project:

.. code:: bash

    composer require comodojo/dispatcher.framework

Requirements
------------

To work properly, dispatcher requires a webserver and PHP >= 5.6.0.

Rewrite rules
-------------

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
