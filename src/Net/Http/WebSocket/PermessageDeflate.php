<?php

namespace Orolyn\Net\Http\WebSocket;

use Orolyn\ArgumentOutOfRangeException;
use Orolyn\Collection\ArrayList;
use Orolyn\Collection\IDictionary;
use Orolyn\Collection\IList;
use Orolyn\Collection\StaticList;
use Orolyn\Net\Http\Header;
use Orolyn\Net\Http\ParameterizedValue;
use Orolyn\Net\Http\ParameterizedValueItem;
use Orolyn\Net\Http\ParameterizedValueItemParameter;

class PermessageDeflate extends Extension
{
    private const MAX_WINDOW_BITS = 15;
    private const MIN_WINDOW_BITS = 8;

    private mixed $inflator = null;
    private mixed $deflator = null;

    /**
     * @param bool $serverNoContextTakeover
     * @param bool $clientNoContextTakeover
     * @param int $serverMaxWindowBits
     * @param int $clientMaxWindowBits
     */
    public function __construct(
        private bool $serverNoContextTakeover,
        private bool $clientNoContextTakeover,
        private int $serverMaxWindowBits,
        private int $clientMaxWindowBits
    ) {
        if ($serverMaxWindowBits > self::MAX_WINDOW_BITS || $serverMaxWindowBits < self::MIN_WINDOW_BITS) {
            throw new ArgumentOutOfRangeException('serverMaxWindowBits');
        }

        if ($clientMaxWindowBits > self::MAX_WINDOW_BITS || $clientMaxWindowBits < self::MIN_WINDOW_BITS) {
            throw new ArgumentOutOfRangeException('clientMaxWindowBits');
        }
    }

    /**
     * @inheritDoc
     */
    public static function create(IList $parameters): static
    {
        $properties = [];

        /** @var ParameterizedValueItemParameter $parameter */
        foreach ($parameters as $parameter) {
            $key = strtolower($parameter->getKey());

            if (array_key_exists($key, $properties)) {
                throw new InvalidExtensionException(sprintf('Duplicate parameter "%s"', $key));
            }

            switch ($key) {
                case 'server_no_context_takeover':
                case 'client_no_context_takeover':
                    if (null !== $parameter->getValue()) {
                        throw new InvalidExtensionException(sprintf('Unexpected value of "%s"', $key));
                    }
                    $value = true;
                    break;
                case 'server_max_window_bits':
                    $value = (int)$parameter->getValue();

                    if ($value > self::MAX_WINDOW_BITS || $value < self::MIN_WINDOW_BITS) {
                        throw new InvalidExtensionException(
                            sprintf(
                                'Invalid value of "%s", must be between %s and %s',
                                $key,
                                self::MIN_WINDOW_BITS,
                                self::MAX_WINDOW_BITS
                            )
                        );
                    }
                    break;
                case 'client_max_window_bits':
                    if (null === $value = (int)$parameter->getValue()) {
                        $value = self::MAX_WINDOW_BITS;
                    }
                    if ($value > self::MAX_WINDOW_BITS || $value < self::MIN_WINDOW_BITS) {
                        throw new InvalidExtensionException(
                            sprintf(
                                'Invalid value of "%s", must be between %s and %s',
                                $key,
                                self::MIN_WINDOW_BITS,
                                self::MAX_WINDOW_BITS
                            )
                        );
                    }
                    break;
                default:
                    throw new InvalidExtensionException(sprintf('Unexpected parameter "%s"', $key));
            }

            $properties[$key] = $value;
        }

        return new PermessageDeflate(
            $properties['server_no_context_takeover'] ?? false,
            $properties['client_no_context_takeover'] ?? false,
            $properties['server_max_window_bits'] ?? self::MAX_WINDOW_BITS,
            $properties['client_max_window_bits'] ?? self::MAX_WINDOW_BITS
        );
    }

    /**
     * @inheritdoc
     */
    public static function getName(): string
    {
        return 'permessage-deflate';
    }

    /**
     * @inheritdoc
     */
    public function createParameterizedValueItem(): ParameterizedValueItem
    {
        $parameters = [];

        if ($this->serverNoContextTakeover) {
            $parameters[] = new ParameterizedValueItemParameter('server_no_context_takeover');
        }

        if ($this->clientNoContextTakeover) {
            $parameters[] = new ParameterizedValueItemParameter('client_no_context_takeover');
        }

        if ($this->serverMaxWindowBits) {
            $parameters[] = new ParameterizedValueItemParameter('server_max_window_bits', $this->serverMaxWindowBits);
        }

        if ($this->clientMaxWindowBits) {
            $parameters[] = new ParameterizedValueItemParameter('client_max_window_bits', $this->clientMaxWindowBits);
        }

        return new ParameterizedValueItem(self::getName(), StaticList::createImmutableList($parameters));
    }

    /**
     * @inheritDoc
     */
    public function encode(Frame $frame, string $bytes): string
    {
        if ($frame->opcode->isControl()) {
            return $bytes;
        }

        $frame->rsv1 = true;

        if ($this->serverNoContextTakeover) {
            $this->deflator = null;
        }

        if (null === $this->deflator) {
            $this->deflator = deflate_init(
                ZLIB_ENCODING_RAW,
                [
                    'level' => -1,
                    'memory' => 8,
                    'window' => $this->serverMaxWindowBits,
                    'strategy' => ZLIB_DEFAULT_STRATEGY
                ]
            );
        }

        return deflate_add($this->deflator, $bytes);
    }

    /**
     * @inheritDoc
     */
    public function decode(Frame $frame, string $bytes): string
    {
        if (!$frame->rsv1) {
            return $bytes;
        }

        if ($this->clientNoContextTakeover) {
            $this->inflator = null;
        }

        if (null === $this->inflator) {
            $this->inflator = inflate_init(
                ZLIB_ENCODING_RAW,
                [
                    'level'    => -1,
                    'memory'   => 8,
                    'window'   => $this->clientMaxWindowBits,
                    'strategy' => ZLIB_DEFAULT_STRATEGY
                ]
            );
        }

        // TODO: something is very broken here. Consecutive reads fail.

        return inflate_add($this->inflator, $bytes);
    }
}
