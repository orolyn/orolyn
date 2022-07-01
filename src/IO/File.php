<?php
namespace Orolyn\IO;

use function Orolyn\Suspend;

class File
{
    /**
     * @var string
     */
    private $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    /**
     * @param string $path
     * @return string
     * @throws FileNotFoundException
     */
    public static function readAllText(string $path): string
    {
        $stream = new FileStream(new File($path));
        $text = $stream->read($stream->getLength());
        $stream->close();;

        return $text;
    }

    public function exists(): bool
    {
        return null !== $this->stat();
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getSize(): int
    {
        if (null === $stat = $this->stat()) {
            throw new FileNotFoundException();
        }

        return $stat['size'];
    }

    public function isAbsolute(): bool
    {
        return str_starts_with($this->path, '/');
    }

    public function getAbsolutePath(): ?string
    {
        if ($this->isAbsolute()) {
            return $this->path;
        }

        return sprintf('%s/%s', getcwd(), $this->path);
    }

    public function getAbsoluteFile(): File
    {
        if ($this->isAbsolute()) {
            return $this;
        }

        return new File($this->getAbsolutePath());
    }

    public function getCanonicalPath(): string
    {
        $complete = false;

        eio_realpath(
            $this->path,
            EIO_PRI_DEFAULT,
            function ($data, $result, $req) use (&$realpath, &$complete) {
                $realpath = $result;
                $complete = true;
            }
        );

        for (;;) {
            eio_poll();

            if ($complete) {
                break;
            }

            Suspend();
        }

        if (-1 === $realpath) {
            throw new FileNotFoundException();
        }

        return $realpath;
    }

    public function getCanonicalFile(): File
    {
        return new File($this->getCanonicalPath());
    }

    public function delete(): void
    {
        $complete = false;

        eio_unlink(
            $this->path,
            EIO_PRI_DEFAULT,
            function ($data, $result, $req) use (&$complete) {
                $complete = true;
            }
        );

        for (;;) {
            eio_poll();

            if ($complete) {
                break;
            }

            Suspend();
        }
    }

    private function stat(): ?array
    {
        $complete = false;

        eio_stat(
            $this->path,
            EIO_PRI_DEFAULT,
            function ($data, $result, $req) use (&$complete, &$stat) {
                $stat = $result;
                $complete = true;
            }
        );

        for (;;) {
            eio_poll();

            if ($complete) {
                break;
            }

            Suspend();
        }

        return is_array($stat) ? $stat : null;

        /*
        $ts=array(
            0140000=>'ssocket',
            0120000=>'llink',
            0100000=>'-file',
            0060000=>'bblock',
            0040000=>'ddir',
            0020000=>'cchar',
            0010000=>'pfifo'
        );

        $p=$stat['mode'];
        $t=decoct($stat['mode'] & 0170000); // File Encoding Bit

        $str =(array_key_exists(octdec($t),$ts))?$ts[octdec($t)]{0}:'u';
        $str.=(($p&0x0100)?'r':'-').(($p&0x0080)?'w':'-');
        $str.=(($p&0x0040)?(($p&0x0800)?'s':'x'):(($p&0x0800)?'S':'-'));
        $str.=(($p&0x0020)?'r':'-').(($p&0x0010)?'w':'-');
        $str.=(($p&0x0008)?(($p&0x0400)?'s':'x'):(($p&0x0400)?'S':'-'));
        $str.=(($p&0x0004)?'r':'-').(($p&0x0002)?'w':'-');
        $str.=(($p&0x0001)?(($p&0x0200)?'t':'x'):(($p&0x0200)?'T':'-'));
        print_r($str);
        */
    }

    public function __toString()
    {
        return $this->path;
    }
}
