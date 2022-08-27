<?php

namespace Orolyn\Net\Security\TLS\Crypto;

use Orolyn\ByteConverter;
use Orolyn\Endian;
use Orolyn\IO\Binary;
use Orolyn\IO\ByteStream;
use Orolyn\Net\Security\TLS\Structure\Alert;
use Orolyn\Net\Security\TLS\Structure\CipherSuite;
use Orolyn\Net\Security\TLS\Structure\ContentType;
use Orolyn\Net\Security\TLS\Structure\ProtocolVersion;
use Orolyn\Net\Security\TLS\Structure\Record;
use Orolyn\Security\Cryptography\SymmetricAlgorithm;

class Encryption
{
    private SymmetricAlgorithm $encryptor;
    private SymmetricAlgorithm $decryptor;
    private int $ivLength;
    private string $encryptIv;
    private string $decryptIv;
    private int $encryptIvSequence = 0;
    private int $decryptIvSequence = 0;

    public function __construct(
        public readonly EncryptionMode $mode,
        public readonly CipherSuite $cipherSuite,
        public Keys $keys
    ) {
        $this->ivLength = $this->cipherSuite->getCipherMethod()->getIvLength();

        if (EncryptionMode::Server === $this->mode) {
            $this->encryptor = new SymmetricAlgorithm(
                $this->cipherSuite->getCipherMethod(),
                $this->keys->serverKey
            );
            $this->decryptor = new SymmetricAlgorithm(
                $this->cipherSuite->getCipherMethod(),
                $this->keys->clientKey
            );
            $this->encryptIv = $this->keys->serverIv;
            $this->decryptIv = $this->keys->clientIv;
        } else {
            $this->encryptor = new SymmetricAlgorithm(
                $this->cipherSuite->getCipherMethod(),
                $this->keys->clientKey
            );
            $this->decryptor = new SymmetricAlgorithm(
                $this->cipherSuite->getCipherMethod(),
                $this->keys->serverKey
            );
            $this->encryptIv = $this->keys->clientIv;
            $this->decryptIv = $this->keys->serverIv;
        }
    }

    public function encrypt(Record $record): Record
    {
        $plainText = $record->bytes . $record->contentType->toByte();

        $header = sprintf(
            '%s%s%s',
            ContentType::ApplicationData->toByte(),
            ProtocolVersion::Version12->toBytes(),
            ByteConverter::getBinaryUnsignedInt16(
                Binary::getLength($plainText) + $this->encryptor->tagLength,
                Endian::BigEndian
            )
        );

        $data = $this->encryptor->encrypt(
            $plainText,
            $iv = $this->incrementIv($this->encryptIv, $this->encryptIvSequence),
            $header
        );

        return new Record(
            ContentType::ApplicationData,
            $data
        );
    }

    public function decrypt(Record $record): Record
    {
        $data = $this->decryptor->decrypt(
            $record->bytes,
            $this->incrementIv($this->decryptIv, $this->decryptIvSequence),
            $record->getHeader()
        );

        return new Record(
            ContentType::fromByte(Binary::getSubstring($data, -1)),
            Binary::getSubstring($data, 0, -1)
        );
    }

    /**
     * @param string $iv
     * @param int $sequence
     * @return string
     */
    private function incrementIv(string $iv, int &$sequence): string
    {
        for ($i = 0; $i < 8; $i++) {
            $index = $this->ivLength - 1 - $i;
            $iv[$index] = $iv[$index] ^ chr(($sequence>>($i * 8)) & 0xFF);
        }

        $sequence++;

        return $iv;
    }
}
