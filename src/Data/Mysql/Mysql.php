<?php

namespace Orolyn\Data\Mysql;

use Orolyn\Data\IDataObjectDriver;
use Orolyn\Endian;
use Orolyn\Math;
use Orolyn\Net\EndPoint;
use Orolyn\Net\Sockets\Socket;
use Orolyn\Net\Uri;

/**
 * @internal
 */
class Mysql implements IDataObjectDriver
{
    private const CAPABILITY_CLIENT_PLUGIN_AUTH = 0x00080000;

    private Socket $socket;

    public function __construct(Uri $uri, array $options = [])
    {
        $host = null;
        $port = 3306;
        $user = $uri->getUser();
        $pass = $uri->getPass();
        $database = null;

        foreach (explode(';', $uri->getPath()) as $item) {
            list ($key, $value) = explode('=', $item);

            switch ($key) {
                case 'host':
                    $host = $value;
                    break;
                case 'port':
                    $port = (int)$value;
                    break;
                case 'dbname':
                    $database = $value;
            }
        }

        $this->socket = new Socket();
        $this->socket->setEndian(Endian::LittleEndian);
        $this->socket->connect(EndPoint::create($host, $port));

        var_dump($this->socket->peek(102));

        $this->readErrorPacket();

        $protocolVersion = $this->socket->readUnsignedInt8();
        $serverVersion = $this->socket->readNullTerminatedString();
        $connectionId = $this->socket->readUnsignedInt32();
        $authPluginData = $this->socket->read(8);
        $this->socket->read(); // reserved
        $capabilityFlags = $this->socket->readUnsignedInt16();
        $characterSet = $this->socket->readUnsignedInt8();
        $statusFlags = $this->socket->readUnsignedInt16();
        $capabilityFlags |= $this->socket->readUnsignedInt16() << 16;
        $authPluginName = null;

        if ($capabilityFlags & MysqlCapability::CLIENT_PLUGIN_AUTH) {
            $authPluginDataLen = $this->socket->readUnsignedInt8();

            $this->socket->read(10); // reserved

            if ($capabilityFlags & MysqlCapability::CLIENT_SECURE_CONNECTION) {
                $authPluginData .= $this->socket->read(Math::max(13, $authPluginDataLen - 8));
                $authPluginName = $this->socket->readNullTerminatedString();
            }
        } else {
            $this->socket->read(10); // reserved
        }

        var_dump($protocolVersion);
        var_dump($serverVersion);
        var_dump($connectionId);
        var_dump($capabilityFlags);
        var_dump($characterSet);
        var_dump($statusFlags);
        var_dump($authPluginData);
        var_dump($authPluginName);

        $this->socket->writeUnsignedInt32(MysqlCapability::CLIENT_PROTOCOL_41);
        $this->socket->writeUnsignedInt32(100);
        $this->socket->writeUnsignedInt8($characterSet);
        $this->socket->write(str_pad('', 23, "\x00"));
        $this->socket->write($user . "\x00");
    }

    private function readErrorPacket(): void
    {
        $header = $this->socket->readInt8();
        $errorCode = $this->socket->readInt16();
        $errorMessage = $this->socket->readNullTerminatedString();
    }
}
