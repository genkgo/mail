<?php
declare(strict_types=1);

namespace Genkgo\Mail;

use Genkgo\Mail\Header\From;
use Genkgo\Mail\Header\HeaderName;
use Genkgo\Mail\Header\ParsedHeader;
use Genkgo\Mail\Header\To;

final class ReplyMessage implements MessageInterface
{
    /**
     * @var MessageInterface
     */
    private $decoratedMessage;

    /**
     * @param MessageInterface $originalMessage
     * @param MessageBodyCollection $body
     * @param QuoteInterface $quotationMethod
     * @param Address $fromAddress
     */
    public function __construct(
        MessageInterface $originalMessage,
        MessageBodyCollection $body,
        QuoteInterface $quotationMethod,
        Address $fromAddress
    )
    {
        $this->decoratedMessage = (new GenericMessage())
            ->withHeader(new From($fromAddress));

        foreach ($originalMessage->getHeader('Message-ID') as $header) {
            $this->decoratedMessage = $this->decoratedMessage->withHeader(
                new ParsedHeader(
                    new HeaderName('In-Reply-To'),
                    $header->getValue()
                )
            );
            break;
        }

        $to = $originalMessage->hasHeader('Reply-To') ? 'Reply-To' : 'To';
        if ($originalMessage->hasHeader($to)) {
            foreach ($originalMessage->getHeader($to) as $header) {
                $this->decoratedMessage = $this->decoratedMessage
                    ->withHeader(
                        new To(AddressList::fromString((string)$header->getValue()))
                    );
            }
        }

        $this->decoratedMessage = $quotationMethod->quote($body, $originalMessage)
            ->attachToMessage($this->decoratedMessage);
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
     * @return iterable|HeaderInterface[]
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
        return $this->decoratedMessage->withHeader($header);
    }

    /**
     * @param HeaderInterface $header
     * @return MessageInterface
     */
    public function withAddedHeader(HeaderInterface $header): MessageInterface
    {
        return $this->decoratedMessage->withAddedHeader($header);
    }

    /**
     * @param string $name
     * @return MessageInterface
     */
    public function withoutHeader(string $name): MessageInterface
    {
        return $this->decoratedMessage->withoutHeader($name);
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
        return $this->decoratedMessage->withBody($body);
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return (string)$this->decoratedMessage;
    }
}
