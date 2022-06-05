=============
Using Sockets
=============

The sockets components provides a simple interface for making TCP connections. The `Socket` class is itself a stream
implementing `IInputStream` and `IOutputStream` allowing for varying data types to be written to and read from the
connection.

Like with all Orolyn IO interfaces, calls to the socket's IO bound APIs block execution, but release control of the
current task when executed within a concurrent environment.

Making a connection
===================

Connection with sockets are performed by providing the necessary endpoint. For example to connect to an IP address
we can use the following example.

Create a netcat server with port `9999`:

.. code-block:: bash

    netcat -k -l 0.0.0.0 9999

.. code-block:: php
    :name: sockets.php

    use Orolyn\Net\IPAddress;
    use Orolyn\Net\IPEndPoint;
    use Orolyn\Net\Sockets\Socket;

    $socket = new Socket();
    $socket->connect(new IPEndPoint(IPAddress::parse('0.0.0.0'), 9999));

    $socket->write("Hello, World!\n");

In this example we can also use the `StreamWriter`:

.. code-block:: php
    :name: sockets.php

    use Orolyn\IO\StreamWriter;

    $writer = new StreamWriter($socket);
    $writer->writeLine('Hello, World!');
    $socket->flush();

Example:

.. code-block:: bash
    :caption: Server terminal

    netcat -k -l 0.0.0.0 9999

.. code-block:: bash
    :caption: Client terminal

    php sockets.php

.. code-block:: text
    :caption: Server output

    Hello, World!
Reading data from a socket
--------------------------

We can also read data from the socket. Here is an example of connecting to an HTTP server and making a simple HTTP
transaction. For this example we are connecting via a DNS endpoint. To learn more about resolving domain names see
documentation on the `DnsResolver <dns.rst>`_.

.. code-block:: php
    :name: domain-connect.php

    $socket = new Socket();
    $socket->connect(new DnsEndPoint('google.com', 80));

    $message = <<<EOF
    GET / HTTP/1.0
    Host: google.com


    EOF;

    $socket->write($message);
    $socket->flush();

    $response = '';

    while (!$socket->isEndOfStream()) {
        if (0 < $available = $socket->getBytesAvailable()) {
            $response .= $socket->read($available);
        }

        usleep(100);
    }

    echo $response;

.. code-block:: bash
    :caption: Client terminal

    php domain-connect.php

.. code-block:: text
    :caption: Output

    HTTP/1.0 301 Moved Permanently
    Location: http://www.google.com/
    Content-Type: text/html; charset=UTF-8
    Date: Sun, 05 Jun 2022 16:55:14 GMT
    Expires: Tue, 05 Jul 2022 16:55:14 GMT
    Cache-Control: public, max-age=2592000
    Server: gws
    Content-Length: 219
    X-XSS-Protection: 0
    X-Frame-Options: SAMEORIGIN

    <HTML><HEAD><meta http-equiv="content-type" content="text/html;charset=utf-8">
    <TITLE>301 Moved</TITLE></HEAD><BODY>
    <H1>301 Moved</H1>
    The document has moved
    <A HREF="http://www.google.com/">here</A>.
    </BODY></HTML>

Concurrency
===========

Guides on concurrency with sockets
