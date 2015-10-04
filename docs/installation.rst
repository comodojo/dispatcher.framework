Installation
============

.. _dispatcher.project: https://github.com/comodojo/dispatcher.project
.. _composer: https://getcomposer.org/
.. _dispatcher.comodojo.org: https://dispatcher.comodojo.org

Comodojo dispatcher could be installed via `composer`_, using dedicated `dispatcher.project`_ package.

Requirements
************

To work properly, dispatcher requires an apache webserver with PHP >=5.3.0, installed as apache module or cgi/fastcgi.

It may work on different webservers like nginx (ensure to convert the .htaccess logic if you plan to use rewrite mode), but this is actually untested.

Installing via composer
***********************

First install `composer`_, then create a new `dispatcher.project`_ using this command::

    php composer.phar create-project comodojo/dispatcher.project dispatcher

This will install a new instance of dispatcher and required dependencies in "dispatcher" folder.

If you need also default content and tests, install the package::

    php composer.phar require comodojo/dispatcher.servicebundle.default

## Downloading as archive

Stable releases are published on `dispatcher.comodojo.org`_.

To install, download latest package and type (in the package folder)::

    php composer.phar install
