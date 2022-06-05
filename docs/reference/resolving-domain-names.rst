======================
Resolving domain names
======================

PHP comes with built-in functions for resolving IP addresses from a domain name:

- `gethostbyname`
- `gethostbynamel`

However the default implementation of these functions block execution. For this reason the Orolyn library includes the
`DnsResolver` class.

Using the DNS Resolver
======================

Calling the static method `lookup` will attempt to fetch all IP addresses associated with the domain name.
Using `getAddress` on the entry result will fetch the first found IP address:

Example:

.. code-block:: php

    use Orolyn\Net\DnsResolver;

    $entry = DnsResolver::lookup('google.com');

    var_dump($entry->getAddress()?->toString());

Output:

.. code-block:: text

    string(14) "142.250.200.14"

Getting all IP addresses
-------------

Calling `getAddressList` will provide a list of all addresses associated with the domain name:

.. code-block:: php

    /** @var IList<IPAddress> */
    $addresses = $entry->getAddressList();

Getting IP addresses asynchronously
===================================

Running multiple searches using the concurrency component enables multiple datagram connections to run without blocking
each other, for example below we will fetch the first available IP address from each of the following domains:

.. code-block:: php

    use Orolyn\Net\DnsResolver;
    use function Orolyn\Lang\Async;
    use function Orolyn\Lang\Await;

    Await(
        $task1 = Async(fn () => DnsResolver::lookup('google.com')),
        $task2 = Async(fn () => DnsResolver::lookup('stackoverflow.com')),
        $task3 = Async(fn () => DnsResolver::lookup('readthedocs.org')),
    );

    var_dump('Task 1: ' . $task1->getResult()->getAddress()->toString());
    var_dump('Task 2: ' . $task2->getResult()->getAddress()->toString());
    var_dump('Task 3: ' . $task3->getResult()->getAddress()->toString());

.. code-block:: text
    :caption: Output

    string(23) "Task 1: 142.250.187.206"
    string(22) "Task 2: 151.101.193.69"
    string(19) "Task 3: 104.18.7.29"
