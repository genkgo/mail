<?php
declare(strict_types=1);

namespace Genkgo\Mail\Mime;

use Genkgo\Mail\HeaderInterface;
use Genkgo\Mail\Stream\EmptyStream;
use Genkgo\Mail\StreamInterface;

final class GenericPart implements PartInterface
{
    /**
     *
     */
    private const ALLOWED_HEADERS = [
        'content-type' => true,
        'content-transfer-encoding' => true,
        'content-id' => true,
        'content-disposition' => true,
        'content-description' => true,
        'content-location' => true,
        'content-language' => true,
    ];
    /**
     * @var array|HeaderInterface[]
     */
    private $headers = [];
    /**
     * @var StreamInterface
     */
    private $body;

    /**
     * Part constructor.
     */
    public function __construct()
    {
        $this->body = new EmptyStream();
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
        $name = strtolower($name);

        return isset($this->headers[$name]);
    }

    /**
     * @param string $name
     * @return HeaderInterface
     */
    public function getHeader(string $name): HeaderInterface
    {
        $name = strtolower($name);

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
        $name = strtolower((string)$header->getName());
        $this->assertValidHeader($name);

        $clone = clone $this;
        $clone->headers[$name] = $header;
        return $clone;
    }

    /**
     * @param string $name
     * @return PartInterface
     */
    public function withoutHeader(string $name): PartInterface
    {
        $name = strtolower($name);

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
        return $this->body;
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