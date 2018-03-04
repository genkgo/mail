<?php
declare(strict_types=1);

namespace Genkgo\Mail\Stream;

use Genkgo\Mail\StreamInterface;

final class OptimalTransferEncodedTextStream implements StreamInterface
{
    /**
     * @var StreamInterface
     */
    private $decoratedStream;

    /**
     * @var string
     */
    private $encoding = '7bit';

    /**
     * @var int
     */
    private $lineLength = 78;
    
    private const NON_7BIT_CHARS = "\x00\x01\x02\x03\x04\x05\x06\x07\x08\x09\x0A\x0B\x0C\x0D\x0E\x0F\x10\x11\x12\x13\x14\x15\x16\x17\x18\x19\x1A\x1B\x1C\x1D\x1E\x1F\x7F\x80\x81\x82\x83\x84\x85\x86\x87\x88\x89\x8A\x8B\x8C\x8D\x8E\x8F\x90\x91\x92\x93\x94\x95\x96\x97\x98\x99\x9A\x9B\x9C\x9D\x9E\x9F\xA0\xA1\xA2\xA3\xA4\xA5\xA6\xA7\xA8\xA9\xAA\xAB\xAC\xAD\xAE\xAF\xB0\xB1\xB2\xB3\xB4\xB5\xB6\xB7\xB8\xB9\xBA\xBB\xBC\xBD\xBE\xBF\xC0\xC1\xC2\xC3\xC4\xC5\xC6\xC7\xC8\xC9\xCA\xCB\xCC\xCD\xCE\xCF\xD0\xD1\xD2\xD3\xD4\xD5\xD6\xD7\xD8\xD9\xDA\xDB\xDC\xDD\xDE\xDF\xE0\xE1\xE2\xE3\xE4\xE5\xE6\xE7\xE8\xE9\xEA\xEB\xEC\xED\xEE\xEF\xF0\xF1\xF2\xF3\xF4\xF5\xF6\xF7\xF8\xF9\xFA\xFB\xFC\xFD\xFE\xFF";

    /**
     * @var string
     */
    private $lineBreak;

    /**
     * @param string $text
     * @param int $lineLength
     * @param string $lineBreak
     */
    public function __construct(string $text, int $lineLength = 78, string $lineBreak = "\r\n")
    {
        $this->lineLength = $lineLength;
        $this->lineBreak = $lineBreak;
        $this->decoratedStream = $this->calculateOptimalStream($text);
    }

    /**
     * @param string $text
     * @return StreamInterface
     */
    private function calculateOptimalStream(string $text): StreamInterface
    {
        if (\strcspn($text, self::NON_7BIT_CHARS) === \strlen($text)) {
            $this->encoding = '7bit';
            return new AsciiEncodedStream($text, $this->lineLength, $this->lineBreak);
        }

        if (\preg_match_all('/[\000-\010\013\014\016-\037\177-\377]/', $text) > (\strlen($text) / 3)) {
            $this->encoding = 'base64';
            return Base64EncodedStream::fromString($text, $this->lineLength, $this->lineBreak);
        }

        $this->encoding = 'quoted-printable';
        return QuotedPrintableStream::fromString($text, $this->lineLength, $this->lineBreak);
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
     * @param array $keys
     * @return array
     */
    public function getMetadata(array $keys = []): array
    {
        $metaData = $this->decoratedStream->getMetadata($keys);
        $metaData['transfer-encoding'] = $this->encoding;

        $keys = \array_map('strtolower', $keys);

        return \array_filter(
            $metaData,
            function ($key) use ($keys) {
                return \in_array(\strtolower($key), $keys);
            },
            ARRAY_FILTER_USE_KEY
        );
    }
}
