<?php
declare(strict_types=1);

namespace Genkgo\Mail\Stream;

use Genkgo\Mail\StreamInterface;

final class QuotedPrintableDecodedStream implements StreamInterface
{
    /**
     * @var StreamInterface
     */
    private $decoratedStream;

    /**
     * @var resource
     */
    private $filter;

    /**
     * @param resource $resource
     */
    public function __construct($resource)
    {
        $this->decoratedStream = new ResourceStream($resource);

        $this->applyFilter();
    }

    /**
     * @param string $string
     * @return QuotedPrintableDecodedStream
     */
    public static function fromString(string $string): QuotedPrintableDecodedStream
    {
        $resource = \fopen('php://memory', 'r+');
        if ($resource === false) {
            throw new \UnexpectedValueException('Cannot open php://memory for writing');
        }

        \fwrite($resource, $string);
        return new self($resource);
    }
    
    private function applyFilter(): void
    {
        $filter = \stream_filter_prepend(
            $this->decoratedStream->detach(),
            'convert.quoted-printable-decode',
            STREAM_FILTER_READ
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
        return null;
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
