<?php
namespace Orolyn\Net;

use Orolyn\Collection\ArrayList;
use Orolyn\Collection\IList;
use Orolyn\StandardObject;

final class IPHostEntry
{
    public function __construct(
        private string $hostName,
        private IList $addressList
    ) {
    }

    public function getHostName(): string
    {
        return $this->hostName;
    }

    /**
     * @return IList<IPAddress>
     */
    public function getAddressList(): IList
    {
        return $this->addressList;
    }
}
