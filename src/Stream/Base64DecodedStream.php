<?php
declare(strict_types=1);

namespace Genkgo\Mail\Stream;

use Genkgo\Mail\StreamInterface;

final class Base64DecodedStream implements StreamInterface
{
    /**
     * @var StreamInterface
     */
    private $decoratedStream;

    /**
     * @param resource $resource
     */
    public function __construct($resource, string $charset = 'UTF-8')
    {
        if ($charset === 'UTF-8') {
            $filters = [
                'convert.base64-decode',
            ];
        } else {
            $filters = [
                'convert.iconv.' . $charset . '/UTF-8',
                'convert.base64-decode',
            ];
        }

        $this->decoratedStream = new FilterStream(new ResourceStream($resource), $filters);
    }

    /**
     * @param string $string
     * @return Base64DecodedStream
     */
    public static function fromString(string $string, string $charset = 'UTF-8'): Base64DecodedStream
    {
        $resource = \fopen('php://memory', 'r+');
        if ($resource === false) {
            throw new \UnexpectedValueException('Cannot open php://memory for writing');
        }

        \fwrite($resource, $string);
        return new self($resource, $charset);
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->decoratedStream->__toString();
    }
    
    public function close(): void
    {
        $this->decoratedStream->close();
    }

    /**
     * @return mixed
     */
    public function detach()
    {
        return $this->decoratedStream->detach();
    }

    public function getSize(): int
    {
        $tell = $this->decoratedStream->tell();
        $size = \strlen((string)$this->decoratedStream);
        $this->decoratedStream->seek($tell);
        return $size;
    }

    /**
     * @return int
     * @throws \RuntimeException
     */
    public function tell(): int
    {
        return $this->decoratedStream->tell();
    }

    /**
     * @return bool
     */
    public function eof(): bool
    {
        return $this->decoratedStream->eof();
    }

    /**
     * @return bool
     */
    public function isSeekable(): bool
    {
        return false;
    }

    /**
     * @param int $offset
     * @param int $whence
     * @return int
     */
    public function seek(int $offset, int $whence = SEEK_SET): int
    {
        return -1;
    }

    /**
     * @return bool
     */
    public function rewind(): bool
    {
        return $this->decoratedStream->rewind();
    }

    /**
     * @return bool
     */
    public function isWritable(): bool
    {
        return $this->decoratedStream->isWritable();
    }

    /**
     * @param string $string
     * @return int
     */
    public function write($string): int
    {
        return $this->decoratedStream->write($string);
    }

    /**
     * @return bool
     */
    public function isReadable(): bool
    {
        return $this->decoratedStream->isReadable();
    }

    /**
     * @param int $length
     * @return string
     */
    public function read(int $length): string
    {
        return $this->decoratedStream->read($length);
    }

    /**
     * @return string
     */
    public function getContents(): string
    {
        return $this->decoratedStream->getContents();
    }

    /**
     * @param array<int, string> $keys
     * @return array<string, mixed>
     */
    public function getMetadata(array $keys = []): array
    {
        return $this->decoratedStream->getMetadata();
    }
}
