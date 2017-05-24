<?php
declare(strict_types=1);

namespace Genkgo\Mail\Mime;

use Genkgo\Mail\Header\ContentDisposition;
use Genkgo\Mail\Header\ContentTransferEncoding;
use Genkgo\Mail\Header\ContentType;
use Genkgo\Mail\HeaderInterface;
use Genkgo\Mail\Stream\Base64EncodedStream;
use Genkgo\Mail\StreamInterface;

final class ResourceAttachment implements PartInterface
{
    /**
     * @var PartInterface
     */
    private $decoratedPart;

    /**
     * @param resource $resource
     * @param string $filename
     * @param ContentType $contentType
     */
    public function __construct($resource, string $filename, ContentType $contentType) {
        if (!is_resource($resource)) {
            throw new \InvalidArgumentException('Resource must be a resource');
        }

        $this->decoratedPart = (new GenericPart())
            ->withBody(new Base64EncodedStream($resource))
            ->withHeader($contentType)
            ->withHeader(ContentDisposition::newAttachment($filename))
            ->withHeader(new ContentTransferEncoding('base64'));
    }

    /**
     * @return Boundary
     */
    public function getBoundary(): Boundary
    {
        throw new \RuntimeException('ResourceAttachment does not have sub parts, so does not have a boundary');
    }

    /**
     * @param Boundary $boundary
     * @return PartInterface
     */
    public function withBoundary(Boundary $boundary): PartInterface
    {
        throw new \RuntimeException('ResourceAttachment does not have sub parts, so cannot not have a boundary');
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
        throw new \RuntimeException('Cannot modify body of ResourcePart');
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
        throw new \BadMethodCallException('FilePart cannot have sub parts. FilePart is a final mime part');
    }

    /**
     * @param PartInterface $part
     * @return PartInterface
     */
    public function withoutPart(PartInterface $part): PartInterface
    {
        throw new \BadMethodCallException('FilePart cannot have sub parts. FilePart is a final mime part');
    }

    /**
     * @param iterable|PartInterface[] $parts
     * @return PartInterface
     */
    public function withParts(iterable $parts): PartInterface
    {
        throw new \BadMethodCallException('FilePart cannot have sub parts. FilePart is a final mime part');
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