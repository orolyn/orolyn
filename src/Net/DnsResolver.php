<?php
namespace Orolyn\Net;

use Orolyn\Collection\ArrayList;
use Orolyn\Endian;
use Orolyn\Event\EventDispatcher;
use Orolyn\Net\Sockets\DatagramPacket;
use Orolyn\Net\Sockets\DatagramSocket;
use function Orolyn\Lang\String;
use function Orolyn\Lang\UnsignedInt16;
use Orolyn\Primitive\TypeString;

class DnsResolver extends EventDispatcher
{
    public const TYPE_A = 1;
    public const TYPE_NS = 2;
    public const TYPE_CNAME = 5;
    public const TYPE_SOA = 6;
    public const TYPE_PTR = 12;
    public const TYPE_MX = 15;
    public const TYPE_TXT = 16;
    public const TYPE_AAAA = 28;
    public const TYPE_ALL = 255;

    private const CLASS_IN = 1;
    private const OPCODE_QUERY = 0;
    private const OPCODE_IQUERY = 1;
    private const OPCODE_STATUS = 2;
    private const RCODE_OK = 0;
    private const RCODE_FORMAT_ERROR = 1;
    private const RCODE_SERVER_FAILURE = 2;
    private const RCODE_NAME_ERROR = 3;
    private const RCODE_NOT_IMPLEMENTED = 4;
    private const RCODE_REFUSED = 5;

    public static function lookup(string $host): ?IPHostEntry
    {
        $socket = new DatagramSocket();
        $socket->connect(new IPEndPoint(IPAddress::parse('8.8.8.8'), 53));

        $packet = new DatagramPacket();
        $packet->setEndian(Endian::BigEndian);

        $header = self::createPacketHeader();
        $header->id = $id = UnsignedInt16(random_bytes(2))->getValue();
        $header->qdCount = 1;
        $header->rd = 1;
        $header->opcode = self::OPCODE_QUERY;
        $header->rcode = self::RCODE_OK;

        $packet->writeUnsignedInt16($header->id);

        $flags = 0x0;
        $flags = ($flags << 1) | $header->qr;
        $flags = ($flags << 4) | $header->opcode;
        $flags = ($flags << 1) | $header->aa;
        $flags = ($flags << 1) | $header->tc;
        $flags = ($flags << 1) | $header->rd;
        $flags = ($flags << 1) | $header->ra;
        $flags = ($flags << 3) | $header->z;
        $flags = ($flags << 4) | $header->rcode;

        $packet->writeUnsignedInt16($flags);
        $packet->writeUnsignedInt16($header->qdCount);
        $packet->writeUnsignedInt16($header->anCount);
        $packet->writeUnsignedInt16($header->nsCount);
        $packet->writeUnsignedInt16($header->arCount);

        foreach (String($host)->explode('.') as $label) {
            $packet->writeUnsignedInt8(String($label)->getLength());
            $packet->write($label);
        }

        $packet->writeUnsignedInt8(0);
        $packet->writeUnsignedInt16(self::TYPE_A);
        $packet->writeUnsignedInt16(self::CLASS_IN);

        $socket->send($packet);

        do {
            $packet = $socket->recv();
            $packet->setEndian(Endian::BigEndian);

            $header = self::createPacketHeader();
            $header->id = $packet->readUnsignedInt16();
        } while ($header->id !== $id);

        $flags = $packet->readUnsignedInt16();

        $header->rcode  =  $flags        & 0x0F;
        $header->z      = ($flags >> 4)  & 0x01;
        $header->ra     = ($flags >> 7)  & 0x01;
        $header->rd     = ($flags >> 8)  & 0x01;
        $header->tc     = ($flags >> 9)  & 0x01;
        $header->aa     = ($flags >> 10) & 0x01;
        $header->opcode = ($flags >> 11) & 0x0F;
        $header->qr     = ($flags >> 15) & 0x01;

        $header->qdCount = $packet->readUnsignedInt16();
        $header->anCount = $packet->readUnsignedInt16();
        $header->nsCount = $packet->readUnsignedInt16();
        $header->arCount = $packet->readUnsignedInt16();

        $qns = self::parseQuestions($packet, $header->qdCount);
        $ans = self::parseAnswers($packet, $header->anCount);
        $nss = self::parseAnswers($packet, $header->nsCount);

        $ipHostEntry = null;

        if ($ans->getLength() > 0) {
            $ipHostEntry = new IPHostEntry($host);

            foreach ($ans as $answer) {
                $ipHostEntry->getAddressList()->add($answer->rdata);
            }
        }

        $socket->close();

        return $ipHostEntry;
    }

    private static function parseQuestions(DatagramPacket $packet, int $count): ArrayList
    {
        $questions = new ArrayList();

        for ($i = 0; $i < $count; $i++) {
            $question = new class () {
                public ?string $name;
                public ?int $type;
                public ?int $class;
            };

            $question->name  = (string)TypeString::join('.', self::parseLabels($packet));
            $question->type  = $packet->readUnsignedInt16();
            $question->class = $packet->readUnsignedInt16();

            $questions->add($question);
        }

        return $questions;
    }

    private static function parseAnswers(DatagramPacket $packet, int $count): ArrayList
    {
        $records = new ArrayList();

        for ($i = 0; $i < $count; $i++) {
            $record = new class () {
                public ?string $name;
                public ?int $type;
                public ?int $class;
                public ?int $ttl;
                public ?int $pref;
                public mixed $rdata;
            };

            $record->name  = (string)TypeString::join('.', self::parseLabels($packet));
            $record->type  = $packet->readUnsignedInt16();
            $record->class = $packet->readUnsignedInt16();
            $record->ttl   = $packet->readUnsignedInt32();

            $rdLength = $packet->readUnsignedInt16();

            switch ($record->type) {
                case self::TYPE_A:
                case self::TYPE_AAAA:
                    $record->rdata = new IPAddress($packet->readInt32());
                    break;
                case self::TYPE_NS:
                case self::TYPE_CNAME:
                case self::TYPE_PTR:
                case self::TYPE_SOA:
                    $record->rdata = (string)TypeString::join('.', self::parseLabels($packet));
                    break;
                case self::TYPE_MX:
                    $record->pref = $packet->readUnsignedInt16();
                    $record->rdata = (string)TypeString::join('.', self::parseLabels($packet));
                    break;
                default:
                    $record->rdata = $packet->read($rdLength);
            }

            $records->add($record);
        }

        return $records;
    }

    public static function parseLabels(DatagramPacket $packet): ArrayList
    {
        $labels = new ArrayList();

        for (;;) {
            if ($packet->readUnsignedInt8() === 0) {
                break;
            }

            $packet->setPosition($packet->getPosition()-1);
            $compressed = $packet->readUnsignedInt16();

            if ($compressed & 0xC000) {
                $offset = $compressed & 0x3FFF;
                $position = $packet->getPosition();
                $packet->setPosition($offset);

                foreach (self::parseLabels($packet) as $label) {
                    $labels->add($label);
                }
                $packet->setPosition($position);

                break;
            } else {
                $packet->setPosition($packet->getPosition() - 2);
                $labels->add($packet->read($packet->readUnsignedInt8()));
            }
        }

        return $labels;
    }

    private static function createPacketHeader(): object
    {
        return new class () {
            public int $id = 0;
            public int $qdCount = 0;
            public int $anCount = 0;
            public int $nsCount = 0;
            public int $arCount = 0;
            public int $qr = 0;
            public int $aa = 0;
            public int $tc = 0;
            public int $rd = 0;
            public int $ra = 0;
            public int $z = 0;
            public int $opcode = 0;
            public int $rcode = 0;
        };
    }
}
