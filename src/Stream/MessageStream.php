<?php
declare(strict_types = 1);

namespace Genkgo\Mail\Stream;

use Genkgo\Mail\Header\HeaderLine;
use Genkgo\Mail\HeaderInterface;
use Genkgo\Mail\MessageInterface;
use Genkgo\Mail\StreamInterface;

final class MessageStream implements StreamInterface
{
    /**
     * @var StreamInterface
     */
    private $decoratedStream;

    /**
     * @param MessageInterface $message
     */
    public function __construct(MessageInterface $message)
    {
        $this->decoratedStream = new LazyStream(function () use ($message) {
            $headerString = \implode(
                "\r\n",
                \array_values(
                    \array_filter(
                        \array_map(
                            function (array $headers) {
                                return \implode(
                                    "\r\n",
                                    \array_map(
                                        function (HeaderInterface $header) {
                                            return (string) (new HeaderLine($header));
                                        },
                                        $headers
                                    )
                                );
                            },
                            $message->getHeaders()
                        )
                    )
                )
            );

            return new ConcatenatedStream(
                new \ArrayObject([
                    new StringStream($headerString),
                    new StringStream("\r\n\r\n"),
                    $message->getBody()
                ])
            );
        });
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
     * @param array $keys
     * @return array
     */
    public function getMetadata(array $keys = []): array
    {
        return $this->decoratedStream->getMetadata();
    }
}
