<?php

namespace Orolyn\IO;

class Buffer
{
    private string $bytes = '';
    private int $length = 0;

    /**
     * @param string $bytes
     */
    public function __construct(string $bytes = '')
    {
        $this->bytes = $bytes;
        $this->length = strlen($bytes);
    }

    /**
     * @param int $length
     * @return string
     */
    public function readAhead(int $length): string
    {
        if ($length > $this->length) {
            $length = $this->length;
        }

        return substr($this->bytes, 0, $length);
    }

    /**
     * @return int
     */
    public function getLength(): int
    {
        return $this->length;
    }

    /**
     * @param $length
     */
    public function skip($length): void
    {
        if ($length > $this->length) {
            $length = $this->length;
        }

        $this->bytes = substr($this->bytes, $length);
        $this->length = strlen($this->bytes);
    }

    /**
     * @param string $bytes
     */
    public function enqueue(string $bytes): void
    {
        $this->bytes .= $bytes;
        $this->length = strlen($this->bytes);
    }

    /**
     * @param int|null $length
     * @return string
     */
    public function dequeue(int $length): string
    {
        if ($length > $this->length) {
            $length = $this->length;
        }

        $bytes = substr($this->bytes, 0, $length);
        $this->bytes = substr($this->bytes, $length);
        $this->length = strlen($this->bytes);

        return $bytes;
    }

    /**
     * @param string $bytes
     * @return void
     */
    public function unshift(string $bytes): void
    {
        $this->bytes = $bytes . $this->bytes;
        $this->length = strlen($this->bytes);
    }

    /**
     * @return string
     */
    public function restart(): string
    {
        $bytes = $this->bytes;
        $this->bytes = '';
        $this->length = 0;

        return $bytes;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->bytes;
    }
}
