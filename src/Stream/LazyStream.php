<?php
declare(strict_types=1);

namespace Genkgo\Mail\Stream;

use Genkgo\Mail\StreamInterface;

final class LazyStream implements StreamInterface
{
    /**
     * @var \Closure
     */
    private $callback;

    /**
     * @var StreamInterface
     */
    private $decoratedStream;

    /**
     * @param \Closure $callback
     */
    public function __construct(\Closure $callback)
    {
        $this->callback = $callback;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->getStream()->__toString();
    }
    
    public function close(): void
    {
        $this->getStream()->close();
    }

    /**
     * @return mixed
     */
    public function detach()
    {
        return $this->getStream()->detach();
    }

    /**
     * @return int|null
     */
    public function getSize(): ?int
    {
        return $this->getStream()->getSize();
    }

    /**
     * @return int
     * @throws \RuntimeException
     */
    public function tell(): int
    {
        return $this->getStream()->tell();
    }

    /**
     * @return bool
     */
    public function eof(): bool
    {
        return $this->getStream()->eof();
    }

    /**
     * @return bool
     */
    public function isSeekable(): bool
    {
        return $this->getStream()->isSeekable();
    }

    /**
     * @param int $offset
     * @param int $whence
     * @return int
     */
    public function seek(int $offset, int $whence = SEEK_SET): int
    {
        return $this->getStream()->seek($offset, $whence);
    }

    /**
     * @return bool
     */
    public function rewind(): bool
    {
        return $this->getStream()->rewind();
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
        return $this->getStream()->read($length);
    }

    /**
     * @return string
     */
    public function getContents(): string
    {
        return $this->getStream()->getContents();
    }

    /**
     * @param array $keys
     * @return array
     */
    public function getMetadata(array $keys = []): array
    {
        return $this->getStream()->getMetadata();
    }

    /**
     * @return StreamInterface
     */
    private function getStream(): StreamInterface
    {
        if ($this->decoratedStream === null) {
            $callback = $this->callback;
            $this->decoratedStream = $callback();
        }

        return $this->decoratedStream;
    }
}
