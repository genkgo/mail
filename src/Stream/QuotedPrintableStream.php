<?php
declare(strict_types=1);

namespace Genkgo\Mail\Stream;

use Genkgo\Mail\StreamInterface;

final class QuotedPrintableStream implements StreamInterface
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
    public function __construct($resource, int $lineLength = 75, string $lineBreak = "\r\n")
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
     * @return QuotedPrintableStream
     */
    public static function fromString(string $string, int $lineLength = 75, string $lineBreak = "\r\n"): QuotedPrintableStream
    {
        $string = \str_replace(
            ["\r\n", "\r", "\n", "\t\r\n", " \r\n"],
            ["\n", "\n", "\r\n", "\r\n", "\r\n"],
            $string
        );

        $resource = \fopen('php://memory', 'r+');
        \fwrite($resource, $string);
        return new self($resource, $lineLength, $lineBreak);
    }
    
    private function applyFilter(): void
    {
        $this->filter = \stream_filter_prepend(
            $this->decoratedStream->detach(),
            'convert.quoted-printable-encode',
            STREAM_FILTER_READ,
            [
                'line-length' => $this->lineLength,
                'line-break-chars' => $this->lineBreak
            ]
        );
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
     * @param array $keys
     * @return array
     */
    public function getMetadata(array $keys = []): array
    {
        return $this->decoratedStream->getMetadata();
    }
}
