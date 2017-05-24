<?php
declare(strict_types=1);

namespace Genkgo\Mail\Mime;

use Genkgo\Mail\HeaderInterface;
use Genkgo\Mail\Stream\ConcatenatedStream;
use Genkgo\Mail\Stream\EmptyStream;
use Genkgo\Mail\Stream\StringStream;
use Genkgo\Mail\StreamInterface;

final class GenericPart implements PartInterface
{
    /**
     *
     */
    private const ALLOWED_HEADERS = [
        'Content-Type' => true,
        'Content-Transfer-Encoding' => true,
        'Content-ID' => true,
        'Content-Disposition' => true,
        'Content-Description' => true,
        'Content-Location' => true,
        'Content-Language' => true,
    ];
    /**
     * @var array|HeaderInterface[]
     */
    private $headers = [];
    /**
     * @var array|PartInterface[]
     */
    private $parts = [];

    /**
     * @var StreamInterface
     */
    private $body;
    /**
     * @var Boundary
     */
    private $boundary;

    /**
     * Part constructor.
     */
    public function __construct()
    {
        $this->body = new EmptyStream();
    }

    /**
     * @return Boundary
     */
    public function getBoundary(): Boundary
    {
        return $this->boundary;
    }

    /**
     * @param Boundary $boundary
     * @return PartInterface
     */
    public function withBoundary(Boundary $boundary): PartInterface
    {
        $clone = clone $this;
        $clone->boundary = $boundary;
        return $clone;
    }

    /**
     * @return iterable
     */
    public function getHeaders(): iterable
    {
        return $this->headers;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasHeader(string $name): bool
    {
        return isset($this->headers[$name]);
    }

    /**
     * @param string $name
     * @return HeaderInterface
     */
    public function getHeader(string $name): HeaderInterface
    {
        if (!isset($this->headers[$name])) {
            throw new \UnexpectedValueException('No header with name ' . $name);
        }

        return $this->headers[$name];
    }

    /**
     * @param HeaderInterface $header
     * @return PartInterface
     */
    public function withHeader(HeaderInterface $header): PartInterface
    {
        $this->assertValidHeader((string)$header->getName());

        $clone = clone $this;
        $clone->headers[(string)$header->getName()] = $header;
        return $clone;
    }

    /**
     * @param string $name
     * @return PartInterface
     */
    public function withoutHeader(string $name): PartInterface
    {
        $clone = clone $this;
        unset($clone->headers[$name]);
        return $clone;
    }

    /**
     * @param StreamInterface $body
     * @return PartInterface
     */
    public function withBody(StreamInterface $body): PartInterface
    {
        $clone = clone $this;
        $clone->body = $body;
        return $clone;
    }

    /**
     * @return StreamInterface
     */
    public function getBody(): StreamInterface
    {
        $streams = new \ArrayObject([$this->body]);

        foreach ($this->parts as $part) {
            $streams->append(new StringStream("\r\n"));
            $streams->append(new StringStream('--' . (string) $this->boundary));
            $streams->append(new StringStream("\r\n"));
            $streams->append($part->toStream());
        }

        if ($this->parts) {
            $streams->append(new StringStream('--' . (string) $this->boundary . '--'));
            $streams->append(new StringStream("\r\n"));
        }

        return new ConcatenatedStream($streams);
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return (string) $this->toStream();
    }

    /**
     * @param PartInterface $part
     * @return PartInterface
     */
    public function withPart(PartInterface $part): PartInterface
    {
        $clone = clone $this;
        $clone->parts[] = $part;
        return $clone;
    }

    /**
     * @param PartInterface $part
     * @return PartInterface
     */
    public function withoutPart(PartInterface $part): PartInterface
    {
        $clone = clone $this;
        $key = array_search($part, $clone->parts, true);
        unset($clone->parts[$key]);
        return $clone;
    }

    /**
     * @param iterable|PartInterface[] $parts
     * @return PartInterface
     */
    public function withParts(iterable $parts): PartInterface
    {
        $clone = clone $this;

        foreach ($parts as $part) {
            $clone->parts[] = $part;
        }

        return $clone;
    }

    /**
     * @return iterable|PartInterface[]
     */
    public function getParts(): iterable
    {
        return $this->parts;
    }

    /**
     * @return StreamInterface
     */
    public function toStream(): StreamInterface
    {
        $streams = new \ArrayObject();
        $streams->append(new StringStream($this->createHeaderLines()));
        $streams->append(new StringStream("\r\n\r\n"));
        $streams->append($this->getBody());

        if ($this->body->getSize() !== 0) {
            $streams->append(new StringStream("\r\n"));
        }

        return new ConcatenatedStream($streams);
    }

    /**
     * @return string
     */
    private function createHeaderLines(): string
    {
        return implode(
            "\r\n",
            array_values(
                array_map(
                    function (HeaderInterface $header) {
                        $headerName = (string)$header->getName();
                        $headerValue = (string)$header->getValue();

                        $firstFoldingAt = strpos($headerValue, "\r\n");
                        if ($firstFoldingAt === false) {
                            $firstFoldingAt = strlen($headerValue);
                        }

                        if (strlen($headerName) + $firstFoldingAt > 76) {
                            return sprintf("%s:\r\n %s", $headerName, $headerValue);
                        }

                        return sprintf("%s: %s", $headerName, $headerValue);
                    },
                    $this->headers
                )
            )
        );
    }

    /**
     * @param string $name
     */
    private function assertValidHeader(string $name)
    {
        if (!isset(self::ALLOWED_HEADERS[$name])) {
            throw new \InvalidArgumentException('Invalid Mime part header ' . $name);
        }
    }
}