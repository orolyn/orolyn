=======
Sockets
=======

Making a connection
===================

Connection with sockets are performed by providing the necessary endpoint. For example to connect to an IP address
we can use the following example.

Create a netcat servers with port `9999`:

.. code-block:: bash

    netcat -k -l 0.0.0.0 9999

.. code-block:: php
    :name: sockets.php

    use Orolyn\Net\IPAddress;
    use Orolyn\Net\IPEndPoint;
    use Orolyn\Net\Sockets\Socket;

    require_once 'vendor/autoload.php';

    $socket = new Socket();
    $socket->connect(new IPEndPoint(IPAddress::parse('0.0.0.0'), 9999));

    $socket->write("Hello, World!\n");

In this example we can also use the `StreamWriter`:

    use Orolyn\IO\StreamWriter;

    $writer = new StreamWriter($socket);
    $writer->writeLine('Hello, World!');
    $socket->flush();

Example:

.. code-block:: bash
    :caption: Server Terminal

    netcat -k -l 0.0.0.0 9999

Output:

.. code-block:: text

    Hello, World!

.. code-block:: bash
    :caption: Client Terminal

    php sockets.php

Connecting via domain name
--------------------------

We can also connect via a domain name. Here is a simple example of connecting to an HTTP server and making a simple
HTTP transaction:

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
    :caption: Client Terminal

    php domain-connect.php

Output:

.. code-block:: text

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
