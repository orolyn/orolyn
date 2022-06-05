================================
Concurrent Synchronous Operation
================================

The Orolyn library uses fibers as its sole means of performing concurrent operations. This is represented in the
form of tasks, and the functionality is exposed by a group of functions which interfaces with the `TaskScheduler`.

The aim of this component, and of the library itself, is to enable performing asynchronous operations in a familiar
synchronous fashion.` While PHP has many tools and extensions available for performing normally blocking operations
asynchronously, the aim here is to perform these operations in a synchronous fashion, i.e. without the use of callbacks
or polling.

Part of this is achieved by designing the provided IO components in such a way so to hide the polling operations, and
secondly by employing a task scheduler which loops over fibers.

For example:

.. code-block:: php

    use function Orolyn\Lang\Async;

    Async(fn () => var_dump('Hello, World!'));

In this example, we are creating an asynchronous task which outputs `"Hello, World!"`. This internal function executes
immediately, so currently there isn't any need for it to be asynchronous. However, we can add suspensions to the
closure to better demonstrate what the `Async` function does.

.. code-block:: php

    use function Orolyn\Lang\Async;
    use function Orolyn\Lang\Suspend;

    $task = Async(
        function () {
            var_dump('Hello, World');
            Suspend();
            var_dump('Goodbye, World');
        }
    );

    var_dump('Something in between');

    $task->wait();

.. code-block:: text
    :caption: Output

    string(12) "Hello, World"
    string(20) "Something in between"
    string(14) "Goodbye, World"

So calling `Async` will execute the closure up until it hits the first `Suspend`.Then a call to `->wait` or
`->getResult` will continue the closure until completion, or a call to `->resume` will continue until the next
`suspend`.

Here is an example of running more than one tasks at once:

.. code-block:: php

    Await(
        Async(
            function () {
                var_dump('Human: Hello, World!');
                Suspend();
                var_dump('Human: Goodbye, World!');
            }
        ),
        Async(
            function () {
                var_dump('World: Hello, Human!');
                Suspend();
                var_dump('World: Goodbye, Human!');
            }
        )
    );

.. code-block:: text
    :caption: Output

    string(20) "Human: Hello, World!"
    string(20) "World: Hello, Human!"
    string(22) "Human: Goodbye, World!"
    string(22) "World: Goodbye, Human!"

We can see that the loop alternatives between the closures on suspend.

So, these have been simple examples, however as mentioned, the rest of this library has been designed to perform
synchronous-like operations in such as way so to release control of the current stack when they hit an IO block. For
example, a stream which is being read from, might not immediately have available data.

Here we will make 20 consecutive calls to Stackoverflow. Firstly, the setup function which will make the call:

.. code-block:: php

    function make_request(string $domain): string
    {
        $request = <<<EOF
    GET / HTTP/1.0
    Host: {$domain}


    EOF;

        $socket = new Socket();
        $socket->connect(new DnsEndPoint('google.com', 80));
        $socket->write($request);
        $socket->flush();

        $output = '';

        while (!$socket->isEndOfStream()) {
            $output .= $socket->read();
        }

        return $output;
    }

Next we will call this function 20 times and measure the time:

.. code-block:: php

    $time = microtime(true);

    for ($i = 0; $i < 20; $i++) {
        make_request('stackoverflow.com');
    }

    var_dump(microtime(true) - $time);

And the time was around half a second:

.. code-block:: text
    :caption: Output

    float(0.5648369789123535)

Next we will perform the socket connection and read/writes 20 times concurrently:

.. code-block:: php

    $time = microtime(true);

    $tasks = [];

    for ($i = 0; $i < 20; $i++) {
        $tasks[] = Async(fn () => make_request('stackoverflow.com'));
    }

    Await($tasks);

    var_dump(microtime(true) - $time);

And the time now is much shorter:

.. code-block:: text
    :caption: Output

    float(0.04011201858520508)

Essentially what this provides is a way to communicate with multiple sockets concurrently and without callbacks.
Because, the connection, writing, the checking of connection status, and the reading are performed internally with
polls which suspend execution of the current task.
