<?php
declare(strict_types=1);

namespace Genkgo\Mail\Mime;

use Genkgo\Mail\Header\ContentDisposition;
use Genkgo\Mail\Header\ContentID;
use Genkgo\Mail\Header\ContentTransferEncoding;
use Genkgo\Mail\Header\ContentType;
use Genkgo\Mail\HeaderInterface;
use Genkgo\Mail\Stream\Base64EncodedStream;
use Genkgo\Mail\StreamInterface;

final class EmbeddedImage implements PartInterface
{
    /**
     * @var PartInterface
     */
    private $decoratedPart;

    /**
     * @param StreamInterface $image
     * @param string $filename
     * @param ContentType $contentType
     * @param ContentID $contentID
     */
    public function __construct(StreamInterface $image, string $filename, ContentType $contentType, ContentID $contentID) {
        $this->decoratedPart = (new GenericPart())
            ->withBody(new Base64EncodedStream($image->detach()))
            ->withHeader(new ContentTransferEncoding('base64'))
            ->withHeader(ContentDisposition::newInline(basename($filename)))
            ->withHeader($contentType)
            ->withHeader($contentID);
    }

    /**
     * @return Boundary
     */
    public function getBoundary(): Boundary
    {
        throw new \RuntimeException('Embedded image does not have sub parts, so does not have a boundary');
    }

    /**
     * @param Boundary $boundary
     * @return PartInterface
     */
    public function withBoundary(Boundary $boundary): PartInterface
    {
        throw new \RuntimeException('Embedded image does not have sub parts, so cannot not have a boundary');
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
        throw new \RuntimeException('Cannot modify body of EmbeddedImagePart');
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
        throw new \BadMethodCallException(
            'EmbeddedImagePart cannot have sub parts. EmbeddedImagePart is a final mime part'
        );
    }

    /**
     * @param PartInterface $part
     * @return PartInterface
     */
    public function withoutPart(PartInterface $part): PartInterface
    {
        throw new \BadMethodCallException(
            'EmbeddedImagePart cannot have sub parts. EmbeddedImagePart is a final mime part'
        );
    }

    /**
     * @param iterable|PartInterface[] $parts
     * @return PartInterface
     */
    public function withParts(iterable $parts): PartInterface
    {
        throw new \BadMethodCallException(
            'EmbeddedImagePart cannot have sub parts. EmbeddedImagePart is a final mime part'
        );
    }

    /**
     * @return iterable|GenericPart[]
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