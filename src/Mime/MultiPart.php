<?php
declare(strict_types=1);

namespace Genkgo\Mail\Mime;

use Genkgo\Mail\GenericMessage;
use Genkgo\Mail\Header\ContentType;
use Genkgo\Mail\Header\ParsedHeader;
use Genkgo\Mail\Header\HeaderValueParameter;
use Genkgo\Mail\HeaderInterface;
use Genkgo\Mail\MessageInterface;
use Genkgo\Mail\Stream\EmptyStream;
use Genkgo\Mail\Stream\LineIterator;
use Genkgo\Mail\StreamInterface;

final class MultiPart implements MultiPartInterface
{
    /**
     * @var PartInterface
     */
    private $decoratedPart;

    /**
     * @var Boundary
     */
    private $boundary;

    /**
     * @var iterable|PartInterface[]
     */
    private $parts = [];

    /**
     * @param Boundary $boundary
     * @param ContentType $contentType
     */
    public function __construct(Boundary $boundary, ContentType $contentType)
    {
        $this->boundary = $boundary;

        if (\substr((string)$contentType->getValue(), 0, 10) !== 'multipart/') {
            throw new \InvalidArgumentException('Content type must be of type multipart/type');
        }

        $this->decoratedPart = (new GenericPart())
            ->withHeader(
                new ParsedHeader(
                    $contentType->getName(),
                    $contentType->getValue()
                        ->withParameter(
                            new HeaderValueParameter(
                                'boundary',
                                (string)$boundary
                            )
                        )
                )
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
        throw new \RuntimeException('Cannot modify headers of MultiPart');
    }

    /**
     * @param string $name
     * @return PartInterface
     */
    public function withoutHeader(string $name): PartInterface
    {
        throw new \RuntimeException('Cannot modify headers of MultiPart');
    }

    /**
     * @param StreamInterface $body
     * @return PartInterface
     */
    public function withBody(StreamInterface $body): PartInterface
    {
        throw new \RuntimeException('Cannot modify body of MultiPart');
    }

    /**
     * @return StreamInterface
     */
    public function getBody(): StreamInterface
    {
        return new EmptyStream();
    }

    /**
     * @return Boundary
     */
    public function getBoundary(): Boundary
    {
        return $this->boundary;
    }

    /**
     * @param PartInterface $part
     * @return MultiPartInterface
     */
    public function withPart(PartInterface $part): MultiPartInterface
    {
        $clone = clone $this;
        $clone->parts[] = $part;
        return $clone;
    }

    /**
     * @param iterable|PartInterface[] $parts
     * @return MultiPartInterface
     */
    public function withParts(iterable $parts): MultiPartInterface
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
     * @param MessageInterface $message
     * @return MultiPart
     */
    public static function fromMessage(MessageInterface $message): self
    {
        foreach ($message->getHeader('Content-Type') as $header) {
            $contentType = $header->getValue()->getRaw();
            if (\substr($contentType, 0, 10) !== 'multipart/') {
                throw new \InvalidArgumentException(
                    \sprintf(
                        'Message is not a multipart/alternative message, but %s',
                        $contentType
                    )
                );
            }

            try {
                $boundary = new Boundary($header->getValue()->getParameter('boundary')->getValue());
            } catch (\UnexpectedValueException $e) {
                throw new \InvalidArgumentException('Message does not contain a boundary');
            }

            $part = new self(
                $boundary,
                new ContentType($header->getValue()->getRaw())
            );

            $content = '';
            $preamble = true;
            foreach (new LineIterator($message->getBody()) as $line) {
                if ($boundary->isOpening($line) && $preamble) {
                    $content = '';
                    $preamble = false;
                    continue;
                }

                if ($boundary->isClosing($line) || $boundary->isOpening($line)) {
                    $message = GenericMessage::fromString(\rtrim($content));

                    try {
                        $part->parts[] = MultiPart::fromMessage($message);
                    } catch (\InvalidArgumentException $e) {
                        $part->parts[] = GenericPart::fromMessage($message);
                    }

                    $content = '';
                }

                if ($boundary->isOpening($line)) {
                    continue;
                }

                if ($boundary->isClosing($line)) {
                    break;
                }

                $content .= $line . "\r\n";
            }

            return $part;
        }

        throw new \InvalidArgumentException('Message is not a multipart/alternative message');
    }
}
