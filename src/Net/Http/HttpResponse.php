<?php
namespace Orolyn\Net\Http;

use Orolyn\IO\IInputStream;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

class HttpResponse extends Message
{
    private int $status;
    private ?string $reasonPhrase;

    /**
     * @param IInputStream|null $body
     * @param int $status
     * @param array $headers
     */
    public function __construct(
        ?IInputStream $body = null,
        int $status = 200,
        array $headers = [],
        string $protocolVersion = '1.0'
    ) {
        parent::__construct($body, $headers, $protocolVersion);

        $this->setStatus($status);
    }

    /**
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->status;
    }

    /**
     * @param $code
     * @param string|null $reasonPhrase
     */
    public function setStatus($code, ?string $reasonPhrase = null): void
    {
        $this->status = $code;
        $this->reasonPhrase = $reasonPhrase ?? StatusText::getStatusText($code);
    }

    /**
     * @return string|null
     */
    public function getReasonPhrase(): ?string
    {
        return $this->reasonPhrase;
    }
}
