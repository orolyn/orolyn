<?php

namespace Orolyn\Net\Http\WebSocket;

class WebSocketMessage
{
    /**
     * @param string $data
     * @param bool $utf8
     */
    public function __construct(
        private string $data,
        private bool $utf8
    ) {
    }

    /**
     * @return string
     */
    public function getData(): string
    {
        return $this->data;
    }

    /**
     * @return bool
     */
    public function isUtf8(): bool
    {
        return $this->utf8;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->data;
    }
}
