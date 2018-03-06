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
    public function __construct($resource, string $filename, ContentType $contentType)
    {
        if (!\is_resource($resource)) {
            throw new \InvalidArgumentException('Resource must be a resource');
        }

        $this->decoratedPart = (new GenericPart())
            ->withBody(new Base64EncodedStream($resource))
            ->withHeader($contentType)
            ->withHeader(new ContentTransferEncoding('base64'))
            ->withHeader(ContentDisposition::newAttachment($filename));
    }

    /**
     * @param string $string
     * @param string $filename
     * @param ContentType $contentType
     * @return ResourceAttachment
     */
    public static function fromString(string $string, string $filename, ContentType $contentType)
    {
        $resource = \fopen('php://memory', 'r+');
        \fwrite($resource, $string);
        return new self($resource, $filename, $contentType);
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
        throw new \RuntimeException('Cannot modify body of ResourceAttachment');
    }

    /**
     * @return StreamInterface
     */
    public function getBody(): StreamInterface
    {
        return $this->decoratedPart->getBody();
    }
}
