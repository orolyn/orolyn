<?php

namespace Orolyn\Net;

use Orolyn\Collection\ArrayList;
use Orolyn\Collection\Dictionary;
use Orolyn\Collection\IList;
use Orolyn\IO\ByteStream;
use Orolyn\IO\File;
use Orolyn\IO\FileNotFoundException;
use Orolyn\IO\StreamWriter;

class HostsFile
{
    private static ?HostsFile $default = null;

    /**
     * @var Dictionary<IPAddress, ArrayList<string>>
     */
    private Dictionary $hosts;

    /**
     * @var Dictionary<string, ArrayList<IPAddress>>
     */
    private Dictionary $addresses;

    /**
     * @param string $contents
     */
    public function __construct(string $contents)
    {
        $this->hosts = new Dictionary();
        $this->addresses = new Dictionary();

        foreach (preg_split('/\r?\n/', $contents) as $line) {
            if (preg_match('/^\s*([^#\s]+)\s+([^#]+\s*)$/', $line, $match)) {
                if (false === $ip = IPAddress::parse($match[1])) {
                    continue;
                }

                $domains = preg_split('/\s+/', $match[2]);

                /** @var ArrayList $hosts */
                if (!$this->hosts->try($ip, $hosts)) {
                    $hosts = new ArrayList();
                    $this->hosts->add($ip, $hosts);
                }

                foreach ($domains as $domain) {
                    $hosts[] = $domain;

                    /** @var ArrayList $addresses */
                    if (!$this->addresses->try($domain, $addresses)) {
                        $addresses = new ArrayList();
                        $this->addresses->add($domain, $addresses);
                    }

                    $addresses[] = $ip;
                }
            }
        }
    }

    /**
     * @return HostsFile
     * @throws FileNotFoundException
     */
    public static function getDefault(): HostsFile
    {
        if (null === self::$default) {
            self::$default = new HostsFile(File::readAllText('/etc/hosts'));
        }

        return self::$default;
    }

    /**
     * @param string $host
     * @return IList
     */
    public function getIPAddressesByHost(string $host): IList
    {
        /** @var ArrayList<IPAddress> $addresses */
        if ($this->addresses->try($host, $addresses)) {
            return $addresses->copy();
        }

        return new ArrayList();
    }

    /**
     * @param string|IPAddress $address
     * @return IList
     */
    public function getHostsByIpAddress(string|IPAddress $address): IList
    {
        if (!$address instanceof IPAddress) {
            $address = IPAddress::parse($address);
        }

        /** @var ArrayList<string> $hosts */
        if ($this->hosts->try($address, $hosts)) {
            return $hosts->copy();
        }

        return new ArrayList();
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        $stream = new ByteStream();
        $writer = new StreamWriter($stream);

        /**
         * @var IPAddress $ipAddress
         * @var ArrayList<string> $hosts
         */
        foreach ($this->hosts as $ipAddress => $hosts) {
            foreach ($hosts as $host) {
                $writer->writeLine(sprintf('%s %s', $ipAddress, $host));
            }
        }

        return $stream;
    }
}
