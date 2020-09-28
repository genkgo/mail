<?php
declare(strict_types=1);

namespace Genkgo\Mail\Stream;

use Genkgo\Mail\HeaderInterface;
use Genkgo\Mail\Mime\PartInterface;
use Genkgo\Mail\StreamInterface;

final class HeaderDecodedStream implements StreamInterface
{
    /**
     * @var StreamInterface
     */
    private $decoratedStream;

    /**
     * @param HeaderInterface $header
     */
    public function __construct(HeaderInterface $header)
    {
        $this->decoratedStream = $this->calculateOptimalStream($header);
    }

    /**
     * @param HeaderInterface $header
     * @return StreamInterface
     */
    private function calculateOptimalStream(HeaderInterface $header): StreamInterface
    {
        $decodedHeader = \iconv_mime_decode((string)$header->getValue(), \ICONV_MIME_DECODE_CONTINUE_ON_ERROR, 'UTF-8');
        if ($decodedHeader === false) {
            throw new \RuntimeException('Cannot decode header');
        }

        return new StringStream((string)$decodedHeader);
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

    /**
     * @return int|null
     */
    public function getSize(): ?int
    {
        return $this->decoratedStream->getSize();
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
        return $this->decoratedStream->isSeekable();
    }

    /**
     * @param int $offset
     * @param int $whence
     * @return int
     */
    public function seek(int $offset, int $whence = SEEK_SET): int
    {
        return $this->decoratedStream->seek($offset, $whence);
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
        return false;
    }

    /**
     * @param string $string
     * @return int
     */
    public function write($string): int
    {
        throw new \RuntimeException('Cannot write to a decoded stream');
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
