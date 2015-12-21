Writing services
================

In dispatcher, services are independent, callable php classes that extends the abstract class `Service` in the `\Comodojo\Dispatcher\Service` namespace.

A service may implement one or more supported HTTP methods defining it's relative php method (in lowercase).

There is an extra special method `setup()` that is used to declare global service properties, like parameters expected in post requests.

Let's take an example::

    class Example extends Service {

        public function setup() {

            $this->setContentType("application/html");

        }

        public function get() {

            return "<h1>This is an example service called via HTTP GET</h1>"

        }

        public function put() {

            return "<h1>This is an example service called via HTTP PUT</h1>"

        }

        public function post() {

            return "<h1>This is an example service called via HTTP POST</h1>"

        }

        public function delete() {

            return "<h1>This is an example service called via HTTP DELETE</h1>"

        }

    }

In example, `setup()` method is used to fix the content type returned by all
methods (default to `text/plain`).

Each method can override this setting using the same setter. In addition, `example`
service implements four HTTP method (GET via `get()`, PUT via `put()`, ...).

Setup method
************

The special `setup()` method is called just after the constructor and *before*
any other method; it may be used to:

- declare required (expects) and optional (likes) parameters
- fix common properties like response headers, content type, ...

Implementing HTTP methods
*************************

As in examples, implementing HTTP methods is quite easy.

New service class should declare one of supported methods (get, put, post, delete) as public,
dispatcher will do the rest.::

    public function get() {

        // This method will be called when someone sends an HTTP GET request to this service path

        // Returned data will be presented to requestor

        return "<h1>This is an example service called via HTTP GET</h1>"

    }

The ANY special method
**********************

There is a special method, the `any()` method, that is called for each HTTP request
method if relative implementation is not defined.

It is a sort of wildcard to create a service that may be called whichever request's HTTP method.::

    class anotherExample extends Service {

        public function any() {

            return 'This is another example service; request HTTP method: '.$_SERVER['REQUEST_METHOD'];

        }

    }

Retrieving attributes and parameters
************************************

The service base class (`Service`) offers different getters to obtain attributes and parameters.

- `getAttributes()`: returns an array of provided attributes. When matched with expected/liked attributes, attribute will come in associative form; otherwise, will be addressed by numeric key.

- `getAttribute($attribute)`: returns the attribute value if provided, or null in other case.

3. `getParameters($raw=false)`: returns an array of provided parameters, if `raw = false`. In the other case, this method will return everyting can be obtained from `php://input`.

4. `getParameter($parameter)`: as for attributes, returns the parameter value or null in case of not found.

Content type & charset
**********************

By default, a service will set HTTP `Content-type` header as `text/plain`.

This behaviour can be changed using the setters:

- `setContentType($type)`

- `setCharset($charset)`

Handling return (HTTP) codes
****************************

(Section yet to be written)

Return data serialization/deserialization
*****************************************

Originally, dispatcher was born to handle json and xml requests.

To facilitate data serialization, simple wrappers for common formats are available
via `$this->serialize` and `$this->deserialize` objects.

Available format serializer/deserializer are:

- array/object from/to json (toJson, fromJson)
- array/object from/to xml (toXml, fromXml)
- array/object from/to yaml (toYaml, fromYaml)

In addition, serialization methods can

- serialize/deserialize data using PHP embedded serialization (toExport)
- dump data on screen via PHP var_export (toDump)

Packaging services
******************

(Section yet to be written)
