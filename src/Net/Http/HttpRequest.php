<?php
namespace Orolyn\Net\Http;

use Orolyn\IO\IInputStream;
use Orolyn\Net\Http\Parser\RequestLine;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

class HttpRequest extends Message
{
    protected ?string $requestTarget;
    protected Uri $uri;
    protected string $method;

    /**
     * @param RequestLine $line
     * @param array $headers
     * @param IInputStream|null $stream
     * @return HttpRequest
     */
    public static function create(RequestLine $line, array $headers, ?IInputStream $stream = null): HttpRequest
    {
        $request = new HttpRequest();

        $request->method = $line->method;
        $request->protocolVersion = $line->version;

        foreach ($headers as $header) {
            $request->addHeader($header);
        }

        $uri = Uri::parseUri($line->path);

        if ($header = $request->getHeader('host')) {
            list($host, $port) = explode(':', $header);

            $uri->setHost($host);
            $uri->setPort($port);
        }

        $request->setUri($uri, true);
        $request->setBody($stream);

        return $request;
    }

    /**
     * @return int
     */
    public function getContentLength(): int
    {
        if (null === $header = $this->getHeader('content-length')) {
            return -1;
        }

        return (int)$header->getValue();
    }

    /**
     * @return string|null
     */
    public function getContentType(): ?string
    {
        if (null === $header = $this->getHeader('content-type')) {
            return null;
        }

        return $header->getValue();
    }

    /**
     * Retrieves the HTTP method of the request.
     *
     * @return string Returns the request method.
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @param string $method
     * @return void
     */
    public function setMethod(string $method): void
    {
        $this->method = $method;
    }

    /**
     * @return Uri
     */
    public function getUri(): Uri
    {
        return $this->uri;
    }

    /**
     * @param Uri $uri
     * @param bool $preserveHost
     * @return void
     */
    public function setUri(Uri $uri, bool $preserveHost = false): void
    {
        $this->uri = $uri;

        if ((null !== $uri->getHost()) && (!$preserveHost || !$this->hasHeader('host')) ) {
            $this->setHeader(new Header('host', $uri->getHost()));
        }
    }
}
