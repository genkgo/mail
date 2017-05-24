<?php
declare(strict_types=1);

namespace Genkgo\Mail\Mime;

use Genkgo\Mail\Header\ContentDisposition;
use Genkgo\Mail\Header\ContentTransferEncoding;
use Genkgo\Mail\Header\ContentType;
use Genkgo\Mail\HeaderInterface;
use Genkgo\Mail\Stream\Base64EncodedStream;
use Genkgo\Mail\StreamInterface;

final class FileAttachment implements PartInterface
{
    /**
     * @var PartInterface
     */
    private $decoratedPart;

    /**
     * @param string $filename
     * @param ContentType $contentType
     * @param string|null $attachmentName
     */
    public function __construct(string $filename, ContentType $contentType, string $attachmentName = '')
    {
        if (!file_exists($filename) || !is_file($filename)) {
            throw new \InvalidArgumentException('Attachment does not exists');
        }

        if ($attachmentName) {
            $attachmentName = basename($filename);
        }

        $resource = fopen($filename, 'r');

        $this->decoratedPart = (new GenericPart())
            ->withBody(new Base64EncodedStream($resource))
            ->withHeader($contentType)
            ->withHeader(ContentDisposition::newAttachment($attachmentName))
            ->withHeader(new ContentTransferEncoding('base64'));
    }

    /**
     * @return Boundary
     */
    public function getBoundary(): Boundary
    {
        throw new \RuntimeException('FileAttachment does not have sub parts, so does not have a boundary');
    }

    /**
     * @param Boundary $boundary
     * @return PartInterface
     */
    public function withBoundary(Boundary $boundary): PartInterface
    {
        throw new \RuntimeException('FileAttachment does not have sub parts, so does not have a boundary');
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
        throw new \BadMethodCallException('FileAttachment cannot have sub parts. FileAttachment is a final mime part');
    }

    /**
     * @param PartInterface $part
     * @return PartInterface
     */
    public function withoutPart(PartInterface $part): PartInterface
    {
        throw new \BadMethodCallException('FileAttachment cannot have sub parts. FileAttachment is a final mime part');
    }

    /**
     * @param iterable|PartInterface[] $parts
     * @return PartInterface
     */
    public function withParts(iterable $parts): PartInterface
    {
        throw new \BadMethodCallException('FileAttachment cannot have sub parts. FileAttachment is a final mime part');
    }

    /**
     * @return iterable|PartInterface[]
     */
    public function getParts(): iterable
    {
        return [];
    }

    /**
     * @return StreamInterface
     */
    public function toStream(): StreamInterface
    {
        return $this->decoratedPart->toStream();
    }
}