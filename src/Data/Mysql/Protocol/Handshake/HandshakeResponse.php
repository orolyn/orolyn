<?php

namespace Orolyn\Data\Mysql\Protocol\Handshake;

use Orolyn\Data\Mysql\MysqlOptions;
use Orolyn\Data\Mysql\Protocol\Authentication\Authentication;
use Orolyn\Data\Mysql\Protocol\Capability;
use Orolyn\Data\Mysql\Protocol\LengthEncoded;
use Orolyn\Data\Mysql\Protocol\Packet;
use Orolyn\IO\Binary;
use Orolyn\IO\ByteStream;

class HandshakeResponse
{
    public function __construct(
        private MysqlOptions $options,
        private int $capabilities,
        private ?Authentication $authentication = null
    ) {
    }

    public function getPayload(): ByteStream
    {
        $output = Packet::createPayload();

        $output->writeUnsignedInt32($this->capabilities);
        $output->writeUnsignedInt32($this->options->maxPacketSize);
        $output->writeUnsignedInt8($this->options->characterSet->getId());
        $output->writeNull(19);

        if (!$this->capabilities & Capability::CLIENT_MYSQL) {
            $output->writeUnsignedInt32($this->capabilities >> 32);
        } else {
            $output->writeNull(4);
        }

        $output->writeNullTerminated($this->options->username);

        if ($this->authentication) {
            $password = $this->authentication->encode($this->options->password);
        } else {
            $password = $this->options->password;
        }

        if ($this->capabilities & Capability::CLIENT_PLUGIN_AUTH_LENENC_CLIENT_DATA) {
            LengthEncoded::encodeLengthEncodedInteger($output, Binary::getLength($password));
            $output->write($password);
        } elseif ($this->capabilities & Capability::CLIENT_SECURE_CONNECTION) {
            $output->writeUnsignedInt8(Binary::getLength($password));
            $output->write(Binary::truncate($password, 0xFF));
        } else {
            $output->writeNullTerminated($password);
        }

        if ($this->capabilities & Capability::CLIENT_CONNECT_WITH_DB) {
            $output->writeNullTerminated($this->options->database);
        }

        if ($this->capabilities & Capability::CLIENT_PLUGIN_AUTH) {
            $output->writeNullTerminated($this->authentication->getName());
        }

        if ($this->capabilities & Capability::CLIENT_CONNECT_ATTRS) {

        }

        return $output;
    }
}
