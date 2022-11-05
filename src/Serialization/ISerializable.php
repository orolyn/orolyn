<?php

namespace Orolyn\Serialization;

interface ISerializable
{
    /**
     * @param SerializationInfo $info
     * @return void
     */
    public function getObjectData(SerializationInfo $info): void;

    /**
     * @param SerializationInfo $info
     * @return void
     */
    public function setObjectData(SerializationInfo $info): void;
}
