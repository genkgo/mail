<?php
declare(strict_types=1);

namespace Genkgo\Mail\Mime;

use Genkgo\Mail\Header\GenericHeader;
use Genkgo\Mail\HeaderInterface;
use Genkgo\Mail\StreamInterface;

final class RelatedPart implements PartInterface
{
    /**
     * @var PartInterface
     */
    private $decoratedPart;

    /**
     * MixedPart constructor.
     * @param Boundary $boundary
     */
    public function __construct(Boundary $boundary)
    {
        $this->decoratedPart = (new GenericPart())
            ->withBoundary($boundary)
            ->withHeader(
                new GenericHeader(
                    'Content-Type',
                    sprintf('multipart/related; boundary="%s"', (string)$boundary)
                )
            );
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
        throw new \RuntimeException('Cannot modify headers of MixedPart');
    }

    /**
     * @param string $name
     * @return PartInterface
     */
    public function withoutHeader(string $name): PartInterface
    {
        throw new \RuntimeException('Cannot modify headers of MixedPart');
    }

    /**
     * @param StreamInterface $body
     * @return PartInterface
     */
    public function withBody(StreamInterface $body): PartInterface
    {
        throw new \RuntimeException('Cannot modify body of FilePart');
    }

    /**
     * @return StreamInterface
     */
    public function getBody(): StreamInterface
    {
        return $this->decoratedPart->getBody();
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->decoratedPart->__toString();
    }

    /**
     * @param PartInterface $part
     * @return PartInterface
     */
    public function withPart(PartInterface $part): PartInterface
    {
        $clone = clone $this;
        $clone->decoratedPart = $clone->decoratedPart->withPart($part);
        return $clone;
    }

    /**
     * @param PartInterface $part
     * @return PartInterface
     */
    public function withoutPart(PartInterface $part): PartInterface
    {
        $clone = clone $this;
        $clone->decoratedPart = $clone->decoratedPart->withoutPart($part);
        return $clone;
    }

    /**
     * @param iterable|PartInterface[] $parts
     * @return PartInterface
     */
    public function withParts(iterable $parts): PartInterface
    {
        $clone = clone $this;
        $clone->decoratedPart = $clone->decoratedPart->withParts($parts);
        return $clone;
    }

    /**
     * @return iterable|PartInterface[]
     */
    public function getParts(): iterable
    {
        return $this->decoratedPart->getParts();
    }

    /**
     * @return StreamInterface
     */
    public function toStream(): StreamInterface
    {
        return $this->decoratedPart->toStream();
    }
}