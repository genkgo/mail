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
        return (string)\stream_get_contents($this->resource);
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
        $stat = \fstat($this->resource);
        if ($stat === false) {
            throw new \UnexpectedValueException('Cannot get stat from resource');
        }

        return $stat['size'];
    }

    /**
     * @return int
     * @throws \RuntimeException
     */
    public function tell(): int
    {
        $tell = \ftell($this->resource);
        if ($tell === false) {
            throw new \UnexpectedValueException('Cannot get tell from resource');
        }

        return $tell;
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
        if (!isset($metaData['seekable'])) {
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
        if (!isset($metaData['uri'])) {
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
        $written = \fwrite($this->resource, $string);
        if ($written === false) {
            throw new \UnexpectedValueException('Cannot write data to resource');
        }

        return $written;
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
        $bytes = \fread($this->resource, $length);
        if ($bytes === false) {
            throw new \UnexpectedValueException('Cannot read from resource');
        }

        return $bytes;
    }

    /**
     * @return string
     */
    public function getContents(): string
    {
        $contents = \stream_get_contents($this->resource);
        if ($contents === false) {
            throw new \UnexpectedValueException('Cannot read contents from resource');
        }

        return $contents;
    }

    /**
     * @param array<int, string> $keys
     * @return array<string, mixed>
     */
    public function getMetadata(array $keys = []): array
    {
        $metaData = \stream_get_meta_data($this->resource);

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
