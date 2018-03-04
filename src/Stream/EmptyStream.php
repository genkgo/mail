<?php
declare(strict_types=1);

namespace Genkgo\Mail\Stream;

use Genkgo\Mail\StreamInterface;

final class EmptyStream implements StreamInterface
{
    /**
     * @return string
     */
    public function __toString(): string
    {
        return '';
    }

    /**
     * @return void
     */
    public function close(): void
    {
        ;
    }

    /**
     * @return null
     */
    public function detach()
    {
        return null;
    }

    /**
     * @return int
     */
    public function getSize(): int
    {
        return 0;
    }

    /**
     * @return int
     */
    public function tell(): int
    {
        return 0;
    }

    /**
     * @return bool
     */
    public function eof(): bool
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isSeekable(): bool
    {
        return true;
    }

    /**
     * @param int $offset
     * @param int $whence
     * @return int
     */
    public function seek(int $offset, int $whence = SEEK_SET): int
    {
        return $offset === 0 ? 0 : -1;
    }

    /**
     * @return bool
     */
    public function rewind(): bool
    {
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
     * @return int on failure.
     */
    public function write($string): int
    {
        throw new \RuntimeException('Cannot write to empty stream');
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
        return '';
    }

    /**
     * @return string
     */
    public function getContents(): string
    {
        return '';
    }

    /**
     * @param array $keys
     * @return array
     */
    public function getMetadata(array $keys = []): array
    {
        return [];
    }
}
