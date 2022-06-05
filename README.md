Orolyn PHP Library
==================

A fiber based asynchronous tooling library for server development.

Documentation
-------------

Documentation is provided at [readthedocs.org](https://orolyn.readthedocs.io/en/latest/)

What is it
----------

This library provides general purpose tools for server development. The primary goal of this library is to enable
development with a familiar synchronous pattern while utilizing fibers to allow for multithreading-like operations.

```php
    use Orolyn\Concurrency\Application;
    use Orolyn\Concurrency\TaskScheduler;
    use Orolyn\Net\Http\FailedHttpRequestException;
    use Orolyn\Net\Http\HttpRequestContext;
    use Orolyn\Net\Http\HttpServer;
    use Orolyn\Net\Http\WebSocket\InvalidWebSocketContextException;
    use Orolyn\Net\Http\WebSocket\WebSocket;
    use Orolyn\Net\Http\WebSocket\WebSocketClosedException;
    use Orolyn\Net\IPAddress;
    use Orolyn\Net\IPEndPoint;
    use function Orolyn\Lang\Async;

    class ApplicationServer extends Application
    {
        public function main(): void
        {
            $httpServer = new HttpServer();
            $httpServer->listen(new IPEndPoint(IPAddress::parse('0.0.0.0'), 9999));

            while ($httpServer->isListening()) {
                try {
                    $context = $httpServer->accept();
                    Async(fn() => $this->handleRequest($context));
                } catch (FailedHttpRequestException $exception) {
                    /* Log error */
                }
            }
        }

        private function handleRequest(HttpRequestContext $context): void
        {
            try {
                $websocket = WebSocket::create($context);
            } catch (InvalidWebSocketContextException $exception) {
                /* Log error */
                return;
            }

            try {
                $websocket->send('Hi!!!!!');
                $data = $websocket->receive()->getData();

                if ('okthxbye' === $data) {
                    $websocket->send('Oh..');
                    $websocket->close();

                    return;
                }

                /* Lets get to work */

            } catch (WebSocketClosedException $exception) {
                return;
            }
        }
    }

    TaskScheduler::run(new ApplicationServer());
```
