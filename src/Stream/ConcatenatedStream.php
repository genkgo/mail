<?php
declare(strict_types=1);

namespace Genkgo\Mail\Stream;

use Genkgo\Mail\StreamInterface;

final class ConcatenatedStream implements StreamInterface
{
    /**
     * @var array|StreamInterface[]
     */
    private $streams = [];

    /**
     * @var int
     */
    private $position = 0;

    /**
     * @var int
     */
    private $index = 0;

    /**
     * @param iterable $streams
     */
    public function __construct(iterable $streams)
    {
        foreach ($streams as $stream) {
            $this->addStream($stream);
        }
    }

    /**
     * @param StreamInterface $stream
     */
    private function addStream(StreamInterface $stream): void
    {
        $this->streams[] = $stream;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        $result = [];

        foreach ($this->streams as $stream) {
            $result[] = $stream->__toString();
        }

        return \implode('', $result);
    }
    
    public function close(): void
    {
        foreach ($this->streams as $stream) {
            $stream->close();
        }
    }

    /**
     * @return mixed
     */
    public function detach()
    {
        return null;
    }

    /**
     * @return int|null
     */
    public function getSize(): ?int
    {
        $size = 0;
        foreach ($this->streams as $stream) {
            $streamSize = $stream->getSize();

            if ($streamSize === null) {
                return null;
            }

            $size += $streamSize;
        }

        return $size;
    }

    /**
     * @return int
     * @throws \RuntimeException
     */
    public function tell(): int
    {
        return $this->position;
    }

    /**
     * @return bool
     */
    public function eof(): bool
    {
        if (!isset($this->streams[$this->index])) {
            return true;
        }

        if (!$this->streams[$this->index]->eof()) {
            return false;
        }

        return isset($this->streams[$this->index + 1]);
    }

    /**
     * @return bool
     */
    public function isSeekable(): bool
    {
        foreach ($this->streams as $stream) {
            if ($stream->isSeekable() === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param int $offset
     * @param int $whence
     * @return int
     */
    public function seek(int $offset, int $whence = SEEK_SET): int
    {
        $seek = 0;
        while (isset($this->streams[$this->index]) && $offset > $seek) {
            $streamSize = $this->streams[$this->index]->getSize();
            if ($this->streams[$this->index]->seek($offset - $seek) === -1) {
                return -1;
            }

            if ($streamSize > $offset - $seek) {
                $seek += $offset - $seek;
            } else {
                $seek += $streamSize;
            }

            if ($this->streams[$this->index]->eof()) {
                $this->index++;
            }
        }

        $this->position = $seek;
        return 0;
    }

    /**
     * @return bool
     */
    public function rewind(): bool
    {
        $this->position = 0;
        $this->index = 0;

        foreach ($this->streams as $stream) {
            $stream->rewind();
        }

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
        $result = '';
        while (\strlen($result) < $length && isset($this->streams[$this->index])) {
            $result .= $this->streams[$this->index]->read($length);
            if ($this->streams[$this->index]->eof()) {
                $this->index++;
            }
        }

        $this->position += \strlen($result);
        return $result;
    }

    /**
     * @return string
     */
    public function getContents(): string
    {
        $result = '';

        while (isset($this->streams[$this->index])) {
            $result .= $this->streams[$this->index]->getContents();
            $this->index++;
        }

        return $result;
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
