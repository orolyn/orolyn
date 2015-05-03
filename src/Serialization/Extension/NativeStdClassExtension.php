<?php
namespace Orolyn\Serialization\Extension;

use Orolyn\Delegate;
use Orolyn\IO\IInputStream;
use Orolyn\IO\IOutputStream;
use Orolyn\Serialization\IExtension;

class NativeStdClassExtension implements IExtension
{
    public function serialize(IOutputStream $stream, $value, Delegate $serialize): \Generator
    {
        $properties = (array)$value;

        yield from $serialize->call(count($properties));

        foreach ($properties as $key => $val) {
            yield from $serialize->call($key);
            yield from $serialize->call($val);
        }
    }

    public function deserialize(IInputStream $stream, &$value, Delegate $deserialize, Delegate $waitBytes, Delegate $addReference)
    {
        $count = yield from $deserialize->call();

        $value = new \stdClass();

        $addReference->call($value);

        for ($i = 0; $i < $count; $i++) {
            $key = yield from $deserialize->call();
            $val = yield from $deserialize->call();

            $value->{$key} = $val;
        }
    }

    public function getType(): string
    {
        return \stdClass::class;
    }
}
