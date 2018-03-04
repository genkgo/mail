<?php
declare(strict_types=1);

namespace Genkgo\Mail;

interface StreamInterface
{
    /**
     * @return string
     */
    public function __toString(): string;
    
    public function close(): void;

    /**
     * @return mixed
     */
    public function detach();

    /**
     * @return int|null
     */
    public function getSize() : ?int ;

    /**
     * @return int
     * @throws \RuntimeException
     */
    public function tell(): int;

    /**
     * @return bool
     */
    public function eof(): bool;

    /**
     * @return bool
     */
    public function isSeekable(): bool;

    /**
     * @param int $offset
     * @param int $whence
     * @return int
     */
    public function seek(int $offset, int $whence = SEEK_SET): int;

    /**
     * @return bool
     */
    public function rewind(): bool;

    /**
     * @return bool
     */
    public function isWritable(): bool;

    /**
     * @param string $string
     * @return int
     */
    public function write($string): int;

    /**
     * @return bool
     */
    public function isReadable(): bool;

    /**
     * @param int $length
     * @return string
     */
    public function read(int $length): string;

    /**
     * @return string
     */
    public function getContents(): string;

    /**
     * @param array $keys
     * @return array
     */
    public function getMetadata(array $keys = []): array;
}
