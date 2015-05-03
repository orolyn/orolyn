<?php
namespace Orolyn\IO;

use Orolyn\ArgumentException;
use Orolyn\ArgumentOutOfRangeException;
use Orolyn\Math;
use Orolyn\NotSupportedException;
use Orolyn\Primitive\TypeString;
use function Orolyn\Lang\String;
use function Orolyn\Lang\Suspend;

class FileStream implements IInputStream, IOutputStream
{
    use EndianTrait;
    use InputStreamTrait;
    use OutputStreamTrait;

    private File $file;
    private int $descriptor;
    private FileStreamOptions $options;
    private int $position = 0;
    private ?Buffer $bytesPending = null;

    /**
     * @param string|File $file
     * @param FileStreamOptions $options
     * @throws FileAlreadyExistsException
     * @throws FileNotFoundException
     * @throws IOException
     */
    public function __construct(string|File $file, FileStreamOptions $options = new FileStreamOptions()) {
        $this->file = $file instanceof File ? $file : new File($file);
        $this->options = clone $options;
        $descriptor = null;

        $flags = 0;

        if (FileAccess::ReadWrite === $options->fileAccess) {
            $flags |= EIO_O_RDWR;
        } elseif (FileAccess::Read === $options->fileAccess) {
            $flags |= EIO_O_RDONLY;
        } elseif (FileAccess::Write === $options->fileAccess) {
            $flags |= EIO_O_WRONLY;
        } else {
            throw new ArgumentException('Mode must be one or both of READ and WRITE');
        }

        $exists = $this->file->exists();

        if (($options->fileMode === FileMode::Open || $options->fileMode === FileMode::Truncate) && !$exists) {
            throw new FileNotFoundException();
        }

        if ($options->fileMode === FileMode::CreateNew  && $exists) {
            throw new FileAlreadyExistsException();
        }

        if (FileAccess::Write === $options->fileAccess || FileAccess::ReadWrite === $options->fileAccess) {
            $flags |= match ($options->fileMode) {
                FileMode::Append => EIO_O_APPEND | EIO_O_CREAT,
                FileMode::Create => $exists ? EIO_O_TRUNC : EIO_O_CREAT,
                FileMode::CreateNew => EIO_O_CREAT | EIO_O_EXCL,
                FileMode::Truncate => EIO_O_TRUNC,
                default => 0
            };
        }

        $req = eio_open(
            $this->file->getPath(),
            $flags,
            0,
            EIO_PRI_DEFAULT,
            function ($data, $result) use (&$descriptor) {
                $descriptor = $result;
            }
        );

        eio_poll();

        while (null === $descriptor) {
            Suspend();
            eio_poll();
        }

        if (-1 === $descriptor) {
            var_dump($req);
            $error = eio_get_last_error($req);

            throw new IOException($error);
        }

        $this->descriptor = $descriptor;
    }

    /**
     * @return void
     */
    public function close(): void
    {
        if ($this->descriptor) {
            eio_close($this->descriptor);
        }
    }

    /**
     * @inheritdoc
     */
    public function peek(int $length = 1): ?string
    {
        $position = $this->position;
        $bytes = $this->read($length);

        if ($this->position !== $position) {
            $this->setPosition($position);
        }

        return $bytes;
    }

    /**
     * @inheritdoc
     */
    public function read(int $length = 1): ?string
    {
        if (!$this->options->fileAccess->canRead()) {
            throw new NotSupportedException('File stream is not open for reading');
        }

        if ($length < 1) {
            throw new ArgumentOutOfRangeException('length');
        }

        if ($this->options->hasEOF) {
            $length = Math::min($length, $this->getBytesAvailable());

            if (0 === $length) {
                return null;
            }
        }

        $bytes = null;

        $req = eio_read(
            $this->descriptor,
            $length,
            -1,
            null,
            function ($data, $result, $req) use (&$success, &$bytes) {
                if (-1 !== $result) {
                    $success = true;
                    $bytes = $result;
                } else {
                    $success = false;
                }
            }
        );

        while (null === $success) {
            Suspend();
            eio_poll();
        }

        if (!$success) {
            throw new IOException(eio_get_last_error($req));
        }

        $this->position += $length;

        return $bytes;
    }

    /**
     * @inheritdoc
     */
    public function write(string $bytes): void
    {
        if (!$this->options->fileAccess->canWrite()) {
            throw new NotSupportedException('File stream is not open for writing');
        }

        $this->bytesPending = new Buffer($bytes);

        do {
            $written = 0;
            $success = null;

            $req = eio_write(
                $this->descriptor,
                $this->bytesPending,
                $this->bytesPending->getLength(),
                -1,
                null,
                function ($data, $result, $req) use (&$success, &$written) {
                    if (-1 !== $result) {
                        $success = true;
                        $written = $result;
                    } else {
                        $success = false;
                    }
                }
            );

            while (null === $success) {
                Suspend();
                eio_poll();
            }

            if (!$success) {
                throw new IOException(eio_get_last_error($req));
            }

            if (FileMode::Append !== $this->options->fileMode) {
                $this->position += $written;
            }

            $this->bytesPending->skip($written);
        } while ($this->bytesPending->getLength() > 0);
    }

    /**
     * @inheritdoc
     */
    public function setPosition(int $position): void
    {
        if ($position < 0) {
            throw new ArgumentOutOfRangeException('length');
        }

        $success = null;

        $req = eio_seek(
            $this->descriptor,
            $position,
            EIO_SEEK_SET,
            null,
            function ($data, $result, $req) use (&$success) {
                $success = -1 !== $result;
            }
        );

        while (null === $success) {
            Suspend();
            eio_poll();
        }

        if (!$success) {
            throw new IOException(eio_get_last_error($req));
        }

        $this->position = $position;
    }

    /**
     * @inheritdoc
     */
    public function getPosition(): int
    {
        return $this->position;
    }

    /**
     * @inheritdoc
     */
    public function isEndOfStream(): bool
    {
        if (!$this->options->hasEOF) {
            return false;
        }

        return $this->position >= $this->getLength();
    }

    /**
     * @inheritdoc
     */
    public function getLength(): int
    {
        $success = null;
        $stat = null;

        $req = eio_fstat(
            $this->descriptor,
            null,
            function ($data, $result, $req) use (&$success, &$stat) {
                $success = -1 !== $result;
                $stat = $result;
            }
        );

        while (null === $success) {
            Suspend();
            eio_poll();
        }

        if (!$success) {
            throw new IOException(eio_get_last_error($req));
        }

        return $stat['size'];
    }

    /**
     * @inheritdoc
     */
    public function getBytesAvailable(): int
    {
        if (!$this->options->fileAccess->canRead()) {
            return 0;
        }

        return Math::max($this->getLength() - $this->position, 0);
    }

    /**
     * @inheritdoc
     */
    public function getBytesPending(): int
    {
        return $this->bytesPending ? $this->bytesPending->getLength() : 0;
    }
}
