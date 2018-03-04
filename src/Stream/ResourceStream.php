<?php
declare(strict_types=1);

namespace Genkgo\Mail\Stream;

use Genkgo\Mail\StreamInterface;

final class ResourceStream implements StreamInterface
{
    /**
     * @var resource
     */
    private $resource;

    /**
     * @param resource $resource
     */
    public function __construct($resource)
    {
        if (!\is_resource($resource)) {
            throw new \InvalidArgumentException('Argument 0 must be a resource');
        }

        \rewind($resource);
        $this->resource = $resource;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        \rewind($this->resource);
        return \stream_get_contents($this->resource);
    }
    
    public function close(): void
    {
        \fclose($this->resource);
    }

    /**
     * @return mixed
     */
    public function detach()
    {
        return $this->resource;
    }

    /**
     * @return int|null
     */
    public function getSize(): ?int
    {
        return \fstat($this->resource)['size'];
    }

    /**
     * @return int
     * @throws \RuntimeException
     */
    public function tell(): int
    {
        return \ftell($this->resource);
    }

    /**
     * @return bool
     */
    public function eof(): bool
    {
        return \feof($this->resource);
    }

    /**
     * @return bool
     */
    public function isSeekable(): bool
    {
        $metaData = \stream_get_meta_data($this->resource);
        if (!$metaData || !isset($metaData['seekable'])) {
            return false;
        }

        return $metaData['seekable'];
    }

    /**
     * @param int $offset
     * @param int $whence
     * @return int
     */
    public function seek(int $offset, int $whence = SEEK_SET): int
    {
        return \fseek($this->resource, $offset, $whence);
    }

    /**
     * @return bool
     */
    public function rewind(): bool
    {
        return \rewind($this->resource);
    }

    /**
     * @return bool
     */
    public function isWritable(): bool
    {
        $metaData = \stream_get_meta_data($this->resource);
        if (!$metaData || !isset($metaData['uri'])) {
            return false;
        }

        return \is_writable($metaData['uri']) || $metaData['uri'] === 'php://memory';
    }

    /**
     * @param string $string
     * @return int
     */
    public function write($string): int
    {
        return \fwrite($this->resource, $string);
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
        return \fread($this->resource, $length);
    }

    /**
     * @return string
     */
    public function getContents(): string
    {
        return \stream_get_contents($this->resource);
    }

    /**
     * @param array $keys
     * @return array
     */
    public function getMetadata(array $keys = []): array
    {
        $metaData = \stream_get_meta_data($this->resource);
        if (!$metaData) {
            return [];
        }

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
