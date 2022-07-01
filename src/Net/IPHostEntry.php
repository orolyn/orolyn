<?php
namespace Orolyn\Net;

use Orolyn\Collection\ArrayList;
use Orolyn\Collection\IList;

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

    /**
     * Returns the first IPAddress if this entry contains any addresses.
     *
     * @return IPAddress|null
     */
    public function getAddress(): ?IPAddress
    {
        if ($this->addressList->isEmpty()) {
            return null;
        }

        return $this->addressList[0];
    }
}
