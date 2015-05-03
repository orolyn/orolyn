<?php

namespace Orolyn\Net\Http\WebSocket;

use Orolyn\Collection\Dictionary;
use Orolyn\Collection\IList;
use Orolyn\Collection\StaticList;
use Orolyn\IO\IInputStream;
use Orolyn\Net\Http\Header;
use Orolyn\Net\Http\ParameterizedValue;
use Orolyn\Net\Http\ParameterizedValueItem;
use Orolyn\Net\Http\ParameterizedValueItemParameter;

abstract class Extension
{
    /**
     * @param IList<ParameterizedValueItemParameter> $parameters
     * @return static
     */
    abstract public static function create(IList $parameters): static;

    /**
     * @param Header|null $header
     * @param IList<class-string<Extension>> $supportedExtensions
     * @return IList<Extension>
     */
    public static function createExtensions(?Header $header, IList $supportedExtensions): IList
    {
        $supportedExtensionsMap = new Dictionary();

        foreach ($supportedExtensions as $extensionClass) {
            $supportedExtensionsMap->add($extensionClass::getName(), $extensionClass);
        }

        $extensions = [];

        if (null !== $header) {
            $extensionValues = ParameterizedValue::parse($header);

            /** @var ParameterizedValueItem $item */
            foreach ($extensionValues as $item) {
                $value = $item->getValue();

                if ($supportedExtensionsMap->containsKey($value)) {
                    $extensionClass = $supportedExtensionsMap->get($value);
                    $extensions[] = $extensionClass::create($item->getParameters());
                }
            }
        }

        return StaticList::createImmutableList($extensions);
    }

    /**
     * @return string
     */
    abstract public static function getName(): string;

    /**
     * @param IList<Extension> $extensions
     * @return Header|null
     */
    public static function createHeader(IList $extensions): ?Header
    {
        if (0 === count($extensions)) {
            return null;
        }

        $values = [];

        foreach ($extensions as $extension) {
            $values[] = $extension->createParameterizedValueItem();
        }

        return new Header(
            'Sec-WebSocket-Extensions',
            ParameterizedValue::createFromItems(StaticList::createImmutableList($values))
        );
    }

    /**
     * @return ParameterizedValueItem
     */
    abstract public function createParameterizedValueItem(): ParameterizedValueItem;

    /**
     * @param Frame $frame
     * @param string $bytes
     * @return string
     */
    abstract public function encode(Frame $frame, string $bytes): string;

    /**
     * @param Frame $frame
     * @param string $bytes
     * @return string
     */
    abstract public function decode(Frame $frame, string $bytes): string;
}
