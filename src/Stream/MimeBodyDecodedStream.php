<?php
declare(strict_types=1);

namespace Genkgo\Mail\Stream;

use Genkgo\Mail\Mime\PartInterface;
use Genkgo\Mail\StreamInterface;

final class MimeBodyDecodedStream implements StreamInterface
{
    /**
     * @var StreamInterface
     */
    private $decoratedStream;

    /**
     * @param PartInterface $mimePart
     */
    public function __construct(PartInterface $mimePart)
    {
        $this->decoratedStream = $this->calculateOptimalStream($mimePart);
    }

    /**
     * @param PartInterface $part
     * @return StreamInterface
     */
    private function calculateOptimalStream(PartInterface $part): StreamInterface
    {
        if (!$part->hasHeader('Content-Transfer-Encoding')) {
            return $part->getBody();
        }

        $encoding = $part->getHeader('Content-Transfer-Encoding')->getValue();
        switch ($encoding) {
            case 'quoted-printable':
                return QuotedPrintableDecodedStream::fromString((string)$part->getBody());
            case 'base64':
                return Base64DecodedStream::fromString((string)$part->getBody());
            case '7bit':
            case '8bit':
                return $part->getBody();
            default:
                throw new \UnexpectedValueException(
                    'Cannot decode body of mime part, unknown transfer encoding ' . $encoding
                );
        }
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
