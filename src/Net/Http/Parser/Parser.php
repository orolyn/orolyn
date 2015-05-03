<?php

namespace Orolyn\Net\Http\Parser;

use Orolyn\FormatException;
use Orolyn\IO\IInputStream;
use Orolyn\IO\StreamReader;
use Orolyn\Net\Http\Header;

class Parser
{
    /**
     * @param IInputStream $stream
     * @return RequestLine
     * @throws FormatException
     */
    public static function parseRequestLine(IInputStream $stream): RequestLine
    {
        $reader = new StreamReader($stream);

        if (!preg_match('/^([a-zA-Z]+)\s+([^\s]+)\s+http\/([\d\.]+)$/i', $reader->readLine(), $match)) {
            throw new FormatException();
        }

        return new RequestLine(
            $match[1],
            $match[2],
            $match[3],
        );
    }

    /**
     * @param IInputStream $stream
     * @return Header|null
     */
    public static function parseHeader(IInputStream $stream): ?Header
    {
        $reader = new StreamReader($stream);
        $line = $reader->readLine();

        if (empty($line)) {
            return null;
        }

        preg_match('/^([^\s:]+)\s*:\s*(.+)/', $line, $match);

        return new Header($match[1], $match[2]);
    }
}
