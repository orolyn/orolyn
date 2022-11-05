<?php

namespace Orolyn\Serialization;

use Orolyn\IO\IOutputStream;

interface IEncodable
{
    /**
     * @param EncodingInfo $info
     * @return void
     */
    public function getEncodedObjectData(EncodingInfo $info): void;

    /**
     * @param DecodingInfo $info
     * @return void
     */
    public function setDecodedObjectData(DecodingInfo $info): void;
}
