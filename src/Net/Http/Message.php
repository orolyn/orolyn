<?php
namespace Orolyn\Net\Http;

use Orolyn\IO\IInputStream;

abstract class Message
{
    protected array $headers = [];

    /**
     * @param IInputStream|null $body
     * @param array $headers
     * @param string $protocolVersion
     */
    public function __construct(
        protected ?IInputStream $body = null,
        array $headers = [],
        protected string $protocolVersion = '1.1'
    ) {
        foreach ($headers as $header) {
            $this->addHeader($header);
        }
    }

    /**
     * @return string
     */
    public function getProtocolVersion(): string
    {
        return $this->protocolVersion;
    }

    /**
     * @param string $version
     */
    public function setProtocolVersion(string $version): void
    {
        $this->protocolVersion = $version;
    }

    /**
     * @return Header[]
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasHeader(string $name): bool
    {
        return array_key_exists(Header::normalizeName($name), $this->headers);
    }

    /**
     * @param string $name
     * @return Header|null
     */
    public function getHeader(string $name): ?Header
    {
        $name = Header::normalizeName($name);

        if (array_key_exists($name, $this->headers)) {
            return $this->headers[$name];
        }

        return null;
    }

    /**
     * @param string $name
     * @return string[]
     */
    public function getHeaderLine(string $name): array
    {
        $name = Header::normalizeName($name);

        if (!array_key_exists($name, $this->headers)) {
            return [];
        }

        $header = $this->headers[$name];
        $lines = [];

        foreach ($header as $value) {
            $lines[] = sprintf('%s: %s', $header->getName(), $value);
        }

        return $lines;
    }

    /**
     * @param string|Header $header
     * @param null|string $value
     */
    public function setHeader(string|Header $header, ?string $value = null): void
    {
        if (!$header instanceof Header) {
            $header = new Header($header, $value);
        }

        $this->headers[$header->getName()] = $header;
    }

    /**
     * @param string|Header $header
     * @param null|string $value
     */
    public function addHeader(string|Header $header, ?string $value = null): void
    {
        if (!$header instanceof Header) {
            $header = new Header($header, $value);
        }

        if ($existing = $this->getHeader($header->getName())) {
            $this->headers[$header->getName()] = Header::mergeHeaders($existing, $header);
        } else {
            $this->setHeader($header);
        }
    }

    /**
     * @param string $name
     */
    public function removeHeader(string $name): void
    {
        $name = Header::normalizeName($name);

        if (array_key_exists($name, $this->headers)) {
            unset($this->headers[$name]);
        }
    }

    /**
     * @return IInputStream|null
     */
    public function getBody(): ?IInputStream
    {
        return $this->body;
    }

    /**
     * @param IInputStream|null $stream
     */
    public function setBody(?IInputStream $stream): void
    {
        $this->body = $stream;
    }
}
