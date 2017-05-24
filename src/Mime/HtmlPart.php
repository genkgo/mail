<?php
declare(strict_types=1);

namespace Genkgo\Mail\Mime;

use Genkgo\Mail\Header\ContentTransferEncoding;
use Genkgo\Mail\Header\ContentType;
use Genkgo\Mail\HeaderInterface;
use Genkgo\Mail\Stream\OptimalTransferEncodedTextStream;
use Genkgo\Mail\StreamInterface;

final class HtmlPart implements PartInterface
{
    /**
     * @var PartInterface
     */
    private $decoratedPart;

    /**
     * @param string $html
     * @param string $charset
     */
    public function __construct(string $html, string $charset = 'UTF-8') {
        if ($html === '') {
            throw new \InvalidArgumentException('Received empty string instead of HTML');
        }

        $stream = new OptimalTransferEncodedTextStream($html);
        $encoding = $stream->getMetadata(['transfer-encoding'])['transfer-encoding'];

        $this->decoratedPart = (new GenericPart())
            ->withHeader(new ContentType('text/html', $charset))
            ->withBody($stream)
            ->withHeader(new ContentTransferEncoding($encoding));
    }

    /**
     * @return Boundary
     */
    public function getBoundary(): Boundary
    {
        return $this->decoratedPart->getBoundary();
    }

    /**
     * @param Boundary $boundary
     * @return PartInterface
     */
    public function withBoundary(Boundary $boundary): PartInterface
    {
        $clone = clone $this;
        $clone->decoratedPart = $this->decoratedPart->withBoundary($boundary);
        return $clone;
    }

    /**
     * @return iterable
     */
    public function getHeaders(): iterable
    {
        return $this->decoratedPart->getHeaders();
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasHeader(string $name): bool
    {
        return $this->decoratedPart->hasHeader($name);
    }

    /**
     * @param string $name
     * @return HeaderInterface
     */
    public function getHeader(string $name): HeaderInterface
    {
        return $this->decoratedPart->getHeader($name);
    }

    /**
     * @param HeaderInterface $header
     * @return PartInterface
     */
    public function withHeader(HeaderInterface $header): PartInterface
    {
        $clone = clone $this;
        $clone->decoratedPart = $this->decoratedPart->withHeader($header);
        return $clone;
    }

    /**
     * @param string $name
     * @return PartInterface
     */
    public function withoutHeader(string $name): PartInterface
    {
        $clone = clone $this;
        $clone->decoratedPart = $this->decoratedPart->withoutHeader($name);
        return $clone;
    }

    /**
     * @param StreamInterface $body
     * @return PartInterface
     */
    public function withBody(StreamInterface $body): PartInterface
    {
        throw new \RuntimeException('Cannot modify body of HtmlPart');
    }

    /**
     * @return StreamInterface
     */
    public function getBody(): StreamInterface
    {
        return $this->decoratedPart->getBody();
    }

    /**
     * @param PartInterface $part
     * @return PartInterface
     */
    public function withPart(PartInterface $part): PartInterface
    {
        throw new \BadMethodCallException('HtmlPart cannot have sub parts. HtmlPart is a final mime part');
    }

    /**
     * @param PartInterface $part
     * @return PartInterface
     */
    public function withoutPart(PartInterface $part): PartInterface
    {
        throw new \BadMethodCallException('HtmlPart cannot have sub parts. HtmlPart is a final mime part');
    }

    /**
     * @param iterable|PartInterface[] $parts
     * @return PartInterface
     */
    public function withParts(iterable $parts): PartInterface
    {
        throw new \BadMethodCallException('HtmlPart cannot have sub parts. HtmlPart is a final mime part');
    }

    /**
     * @return iterable|PartInterface[]
     */
    public function getParts(): iterable
    {
        return [];
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->decoratedPart->__toString();
    }

    /**
     * @return StreamInterface
     */
    public function toStream(): StreamInterface
    {
        return $this->decoratedPart->toStream();
    }
}