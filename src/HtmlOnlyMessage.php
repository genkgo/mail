<?php
declare(strict_types=1);

namespace Genkgo\Mail;

use Genkgo\Mail\Header\ContentTransferEncoding;
use Genkgo\Mail\Header\ContentType;
use Genkgo\Mail\Stream\OptimalTransferEncodedTextStream;

final class HtmlOnlyMessage implements MessageInterface
{
    /**
     * @var MessageInterface
     */
    private $decoratedMessage;

    /**
     * @param string $html
     * @param string $charset
     */
    public function __construct(string $html, string $charset = 'UTF-8')
    {
        $stream = new OptimalTransferEncodedTextStream($html);
        $encoding = $stream->getMetadata(['transfer-encoding'])['transfer-encoding'];

        if ($encoding === '7bit') {
            $charset = 'us-ascii';
        }

        $this->decoratedMessage = (new GenericMessage())
            ->withHeader(new ContentType('text/html', $charset))
            ->withBody($stream)
            ->withHeader(new ContentTransferEncoding($encoding));
    }

    /**
     * @return iterable
     */
    public function getHeaders(): iterable
    {
        return $this->decoratedMessage->getHeaders();
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasHeader(string $name): bool
    {
        return $this->decoratedMessage->hasHeader($name);
    }

    /**
     * @param string $name
     * @return iterable
     */
    public function getHeader(string $name): iterable
    {
        return $this->decoratedMessage->getHeader($name);
    }

    /**
     * @param HeaderInterface $header
     * @return MessageInterface
     */
    public function withHeader(HeaderInterface $header): MessageInterface
    {
        $clone = clone $this;
        $clone->decoratedMessage = $clone->decoratedMessage->withHeader($header);
        return $clone;
    }

    /**
     * @param HeaderInterface $header
     * @return MessageInterface
     */
    public function withAddedHeader(HeaderInterface $header): MessageInterface
    {
        $clone = clone $this;
        $clone->decoratedMessage = $clone->decoratedMessage->withAddedHeader($header);
        return $clone;
    }

    /**
     * @param string $name
     * @return MessageInterface
     */
    public function withoutHeader(string $name): MessageInterface
    {
        $clone = clone $this;
        $clone->decoratedMessage = $clone->decoratedMessage->withoutHeader($name);
        return $clone;
    }

    /**
     * @return StreamInterface
     */
    public function getBody(): StreamInterface
    {
        return $this->decoratedMessage->getBody();
    }

    /**
     * @param StreamInterface $body
     * @return MessageInterface
     */
    public function withBody(StreamInterface $body): MessageInterface
    {
        $clone = clone $this;
        $clone->decoratedMessage = $clone->decoratedMessage->withBody($body);
        return $clone;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->decoratedMessage->__toString();
    }
}
