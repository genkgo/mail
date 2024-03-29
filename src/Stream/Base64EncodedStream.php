<?php
declare(strict_types=1);

namespace Genkgo\Mail\Stream;

use Genkgo\Mail\StreamInterface;

final class Base64EncodedStream implements StreamInterface
{
    /**
     * @var StreamInterface
     */
    private $decoratedStream;

    /**
     * @var int
     */
    private $lineLength;

    /**
     * @var resource
     */
    private $filter;

    /**
     * @var string
     */
    private $lineBreak;

    /**
     * @param resource $resource
     * @param int $lineLength
     * @param string $lineBreak
     */
    public function __construct($resource, int $lineLength = 76, string $lineBreak = "\r\n")
    {
        $this->decoratedStream = new ResourceStream($resource);
        $this->lineLength = $lineLength;
        $this->lineBreak = $lineBreak;

        $this->applyFilter();
    }

    /**
     * @param string $string
     * @param int $lineLength
     * @param string $lineBreak
     * @return Base64EncodedStream
     */
    public static function fromString(string $string, int $lineLength = 76, string $lineBreak = "\r\n"): Base64EncodedStream
    {
        $resource = \fopen('php://memory', 'r+');
        if ($resource === false) {
            throw new \UnexpectedValueException('Cannot open php://memory for writing');
        }

        \fwrite($resource, $string);
        return new self($resource, $lineLength, $lineBreak);
    }
    
    private function applyFilter(): void
    {
        /** @var resource $detached */
        $detached = $this->decoratedStream->detach();

        $filter = \stream_filter_prepend(
            $detached,
            'convert.base64-encode',
            STREAM_FILTER_READ,
            [
                'line-length' => $this->lineLength,
                'line-break-chars' => $this->lineBreak
            ]
        );

        if ($filter === false) {
            throw new \UnexpectedValueException('Cannot append filter to stream');
        }

        $this->filter = $filter;
    }

    private function removeFilter(): void
    {
        if ($this->filter !== null) {
            \stream_filter_remove($this->filter);
        }
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        $this->rewind();
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

    /**
     * @return int|null
     */
    public function getSize(): ?int
    {
        return (int) \ceil($this->decoratedStream->getSize() / 3) * 4;
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
        $this->removeFilter();
        if (!$this->decoratedStream->rewind()) {
            return false;
        }

        $this->applyFilter();
        return true;
    }

    /**
     * @return bool
     */
    public function isWritable(): bool
    {
        return false;
    }

    /**
     * @param string $string
     * @return int
     */
    public function write($string): int
    {
        throw new \RuntimeException('Cannot write to stream');
    }

    /**
     * @return bool
     */
    public function isReadable(): bool
    {
        return true;
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
