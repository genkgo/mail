<?php
declare(strict_types=1);

namespace Genkgo\Mail;

use Genkgo\Mail\Header\MimeVersion;
use Genkgo\Mail\Stream\ConcatenatedStream;
use Genkgo\Mail\Stream\StringStream;

final class MimeMessage implements MessageInterface
{
    /**
     * @var MessageInterface
     */
    private $decoratedMessage;

    /**
     * MimeMessage constructor.
     * @param Mime\Message $message
     */
    public function __construct(Mime\Message $message)
    {
        $this->decoratedMessage = (new GenericMessage())
            ->withHeader(new MimeVersion());

        foreach ($message->getParts() as $part) {
            foreach ($part->getHeaders() as $header) {
                $this->decoratedMessage = $this->decoratedMessage->withHeader($header);
            }

            if ($part->getParts()) {
                $this->decoratedMessage = $this->decoratedMessage->withBody(
                    new ConcatenatedStream(
                        new \ArrayObject([
                            new StringStream("This is a multipart message in MIME format.\r\n"),
                            $part->getBody()
                        ])
                    )
                );
            } else {
                $this->decoratedMessage = $this->decoratedMessage->withBody($part->getBody());
            }

            break;
        }
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