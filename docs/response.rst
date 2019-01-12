.. _response:

The response model
==================

Once the request is routed to an actual service, it is possible to compose a Response object. The router itself will execute the service and provide the resulting output to the Response objcet.

.. code-block:: php

    $response = new \Comodojo\Dispatcher\Response\Model(
        $router->configuration(), 
        $router->logger()
    );
    
    $router->compose($response);
    
    echo $response->content()->get();



.. _response-output-processor:

Output Processor
----------------

The DispatcherException
.......................
