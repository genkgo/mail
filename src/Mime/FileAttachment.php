<?php
declare(strict_types=1);

namespace Genkgo\Mail\Mime;

use Genkgo\Mail\Header\ContentDisposition;
use Genkgo\Mail\Header\ContentTransferEncoding;
use Genkgo\Mail\Header\ContentType;
use Genkgo\Mail\Header\HeaderValue;
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
     * @param string $attachmentName
     */
    public function __construct(string $filename, ContentType $contentType, string $attachmentName = '')
    {
        if (!\file_exists($filename) || !\is_file($filename)) {
            throw new \InvalidArgumentException('Attachment does not exists');
        }

        if ($attachmentName === '') {
            $attachmentName = \basename($filename);
        }

        $resource = \fopen($filename, 'r');

        $this->decoratedPart = (new GenericPart())
            ->withBody(new Base64EncodedStream($resource))
            ->withHeader($contentType)
            ->withHeader(new ContentTransferEncoding('base64'))
            ->withHeader(ContentDisposition::newAttachment($attachmentName));
    }

    /**
     * @param string $filename
     * @param string $attachmentName
     * @return FileAttachment
     */
    public static function fromUnknownFileType(string $filename, string $attachmentName = ''): FileAttachment
    {
        $fileInfo = new \finfo(FILEINFO_MIME);
        $mime = $fileInfo->file($filename);

        $headerValue = HeaderValue::fromString($mime);

        try {
            $charset = $headerValue->getParameter('charset')->getValue();
            if ($charset === 'binary') {
                $contentType = new ContentType($headerValue->getRaw());
            } else {
                $contentType = new ContentType($headerValue->getRaw(), $charset);
            }
        } catch (\UnexpectedValueException $e) {
            $contentType = new ContentType($headerValue->getRaw());
        }

        return new self(
            $filename,
            $contentType,
            $attachmentName
        );
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
            throw new \InvalidArgumentException('Cannot modify content disposition for file attachment');
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
            throw new \InvalidArgumentException('Cannot modify content disposition for file attachment');
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
        throw new \RuntimeException('Cannot modify body of FileAttachment');
    }

    /**
     * @return StreamInterface
     */
    public function getBody(): StreamInterface
    {
        return $this->decoratedPart->getBody();
    }
}
