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
    public function __construct(StreamInterface $image, string $filename, ContentType $contentType, ContentID $contentID)
    {
        $this->decoratedPart = (new GenericPart())
            ->withBody(new Base64EncodedStream($image->detach()))
            ->withHeader($contentType)
            ->withHeader(new ContentTransferEncoding('base64'))
            ->withHeader(ContentDisposition::newInline(\basename($filename)))
            ->withHeader($contentID);
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
        if (\strtolower((string)$header->getName()) === 'content-disposition') {
            throw new \InvalidArgumentException('Cannot modify content disposition for embedded image');
        }

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
        if (\strtolower($name) === 'content-disposition') {
            throw new \InvalidArgumentException('Cannot modify content disposition for embedded image');
        }

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
}
