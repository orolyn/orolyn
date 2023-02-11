<?php

namespace Orolyn\Data\Mysql\Protocol\Authentication\Plugin;

use Orolyn\Data\DriverException;
use Orolyn\Data\Mysql\MysqlHandle;
use Orolyn\Data\Mysql\MysqlOptions;
use Orolyn\Data\Mysql\Protocol\Authentication\Authentication;
use Orolyn\IO\Binary;
use Orolyn\IO\ByteStream;
use Orolyn\Net\Security\TLS\Crypto\Encryption;
use Orolyn\Net\Security\TLS\Crypto\EncryptionMode;
use Orolyn\Security\Cryptography\HashAlgorithm;
use Orolyn\Security\Cryptography\HashAlgorithmMethod;
use Orolyn\Version;

class CachingSha2Password extends Authentication
{
    public const NAME = 'caching_sha2_password';

    private string $salt;

    protected function __construct(string $data)
    {
        $this->salt = $data;
    }

    public function encode(string $password): string
    {
        $algo = HashAlgorithm::create(HashAlgorithmMethod::SHA256);

        $part1 = (string)$algo->computeHash($password);
        $part2 = (string)$algo->computeHash($algo->computeHash($part1) . ($this->salt));

        return $part1 ^ $part2;
    }

    public function continuation(MysqlOptions $options, MysqlHandle $handle): void
    {
        $handle->sendPacket(new ByteStream("\x02"));
        $stream = $handle->getPacket()->payload;

        if (0x01 !== $stream->readUnsignedInt8()) {
            throw new DriverException('Invalid public key response');
        }

        $publicKey = $stream->read($stream->getBytesAvailable());

        /*
        $publicKey = str_replace(["\x0A", "\x0D"], '', );

        $header = '-----BEGIN PUBLIC KEY-----';
        $footer = '-----END PUBLIC KEY-----';

        $publicKey = Binary::getSubstring($publicKey, Binary::getLength($header));
        $publicKey = Binary::getSubstring($publicKey, 0, -Binary::getLength($footer));
        */

        $password = $options->password . "\x00";
        $passwordLength = Binary::getLength($password);
        $seedLength = Binary::getLength($this->salt);
        $obfuscated = '';

        for ($i = 0; $i < $passwordLength; $i++) {
            $obfuscated[$i] = $password[$i] ^ $this->salt[$i % $seedLength];
        }

        $padding = $options->serverVersion->compareTo(Version::parse('8.0.5')) >= 0
            ? OPENSSL_PKCS1_OAEP_PADDING
            : OPENSSL_PKCS1_PADDING;

        // TODO: implement RSACryptoServiceProvider and replace
        openssl_public_encrypt($obfuscated, $data, $publicKey, $padding);

        $handle->sendPacket($data);

    }

    public function getName(): string
    {
        return Authentication::CACHING_SHA2_PASSWORD;
    }
}


