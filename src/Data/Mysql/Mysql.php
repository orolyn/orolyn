<?php

namespace Orolyn\Data\Mysql;

use Orolyn\Console\HexViewer;
use Orolyn\Data\DriverException;
use Orolyn\Data\IDataObjectDriver;
use Orolyn\Data\Mysql\Protocol\Authentication\Authentication;
use Orolyn\Data\Mysql\Protocol\Authentication\AuthSwitchRequest;
use Orolyn\Data\Mysql\Protocol\Authentication\AuthSwitchResponse;
use Orolyn\Data\Mysql\Protocol\Capability;
use Orolyn\Data\Mysql\Protocol\Command\Command;
use Orolyn\Data\Mysql\Protocol\Command\Text\Query;
use Orolyn\Data\Mysql\Protocol\Command\Text\Quit;
use Orolyn\Data\Mysql\Protocol\Handshake\Handshake;
use Orolyn\Data\Mysql\Protocol\Handshake\HandshakeResponse;
use Orolyn\Data\Mysql\Protocol\MysqlCapabilityList;
use Orolyn\Data\Mysql\Protocol\Packet;
use Orolyn\Data\Mysql\Protocol\Response\OK;
use Orolyn\Data\Mysql\Protocol\Response\ResultSet;
use Orolyn\Data\Mysql\Protocol\Response\ServerResponse;
use Orolyn\Endian;
use Orolyn\IO\ByteStream;
use Orolyn\Net\EndPoint;
use Orolyn\Net\Sockets\Socket2;
use Orolyn\Net\Uri;

/**
 * @internal
 */
class Mysql implements IDataObjectDriver
{
    private MysqlHandle $handle;
    private MysqlOptions $options;
    private int $capabilities;
    private ?Authentication $authentication = null;
    private ServerResponse $serverResponse;

    public function __construct(Uri $uri, array $options = [])
    {
        $this->options = new MysqlOptions($options);
        $this->capabilities =
            Capability::CLIENT_MYSQL |
            Capability::CLIENT_PROTOCOL_41 |
            Capability::CLIENT_PLUGIN_AUTH |
            Capability::CLIENT_PLUGIN_AUTH_LENENC_CLIENT_DATA |
            Capability::CLIENT_SECURE_CONNECTION |
            Capability::CLIENT_DEPRECATE_EOF;

        /*
        $this->capabilityList->add(MysqlCapability::CLIENT_MULTI_STATEMENTS);
        $this->capabilityList->add(MysqlCapability::CLIENT_MULTI_RESULTS);
        $this->capabilityList->add(MysqlCapability::CLIENT_PS_MULTI_RESULTS);
        $this->capabilityList->add(MysqlCapability::CLIENT_PLUGIN_AUTH);
        $this->capabilityList->add(MysqlCapability::CLIENT_CONNECT_ATTRS);
        $this->capabilityList->add(MysqlCapability::CLIENT_PLUGIN_AUTH_LENENC_CLIENT_DATA);
        $this->capabilityList->add(MysqlCapability::CLIENT_CAN_HANDLE_EXPIRED_PASSWORDS);
        $this->capabilityList->add(MysqlCapability::CLIENT_SESSION_TRACK);
        $this->capabilityList->add(MysqlCapability::CLIENT_DEPRECATE_EOF);
        */

        $host = null;
        $port = 3306;
        $this->options->username = $uri->getUser();
        $this->options->password = $uri->getPass();
        $this->options->database = null;

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

        $this->handle = new MysqlHandle(EndPoint::create($host, $port));

        $handshake = Handshake::decode($this->handle->getPacket());
        $this->capabilities &= $handshake->capabilities;
        $this->options->characterSet = $handshake->characterSet;
        $this->options->serverVersion = $handshake->serverVersion;

        $this->serverResponse = new ServerResponse($this->options, $this->capabilities, $this->handle);

        if ($this->capabilities & Capability::CLIENT_PLUGIN_AUTH) {
            $this->authentication = Authentication::getPluginFromString(
                $handshake->authPluginName,
                $handshake->authPluginData
            );
        }

        //print_r($handshake);
        //print_r($handshake->capabilityList->getDebugArray());

        $response = new HandshakeResponse($this->options, $this->capabilities, $this->authentication);
        $this->handle->sendPacket($response->getPayload());

        if ($this->capabilities & Capability::CLIENT_PLUGIN_AUTH) {
            //$this->authentication->handle($this->socket, AuthSwitchRequest::decode($this->getPacket()));
            $authSwitchRequest = AuthSwitchRequest::decode($this->handle->getPacket());

            if ($authSwitchRequest->isContinue()) {
                $this->authentication->continuation($this->options, $this->handle);
            } elseif ($authSwitchRequest->isRestart()) {
                // TODO: renegotiate
            }
        }

        $response = $this->serverResponse->decode($this->handle->getPacket()->payload, $this->capabilities);

        if ($response instanceof OK) {
            $this->handle->resetSequence();
        }
    }

    public function exec(string $statement): int|false
    {
        $this->handle->sendCommand(new Query($statement));
        $response = $this->serverResponse->decode($this->handle->getPacket()->payload);
    }

    public function __destruct()
    {
        $this->handle->sendCommand(new Quit());
    }
}
