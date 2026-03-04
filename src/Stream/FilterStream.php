<?php
declare(strict_types=1);

namespace Genkgo\Mail\Stream;

use Genkgo\Mail\StreamInterface;

final class FilterStream implements StreamInterface
{
    /**
     * @var StreamInterface
     */
    private $decoratedStream;

    /**
     * @var array<int, resource>
     */
    private $filters;

    /**
     * @var array<int, string>
     */
    private $filterNames;

    /**
     * @param array<int, string> $filterNames
     */
    public function __construct(StreamInterface $decoratedStream, array $filterNames)
    {
        $this->decoratedStream = $decoratedStream;
        $this->filterNames = $filterNames;

        $this->applyFilter();
    }

    private function applyFilter(): void
    {
        /** @var resource $detached */
        $detached = $this->decoratedStream->detach();

        foreach ($this->filterNames as $filterName) {
            $filter = \stream_filter_prepend(
                $detached,
                $filterName,
                STREAM_FILTER_READ
            );

            if ($filter === false) {
                throw new \UnexpectedValueException('Cannot append filter to stream');
            }

            $this->filters[] = $filter;
        }

    }

    private function removeFilter(): void
    {
        foreach ($this->filters as $filter) {
            \stream_filter_remove($filter);
        }

        $this->filters = [];
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
