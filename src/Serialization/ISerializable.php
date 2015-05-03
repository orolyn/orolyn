<?php
namespace Orolyn\Serialization;

interface ISerializable
{
    /**
     * @param SerializationInfo $info
     */
    public function getObjectData(SerializationInfo $info): void;

    /**
     * @param SerializationInfo $info
     */
    public function setObjectData(SerializationInfo $info): void;
}
