<?php
declare(strict_types=1);

namespace Genkgo\Mail;

use Genkgo\Mail\Header\HeaderLine;
use Genkgo\Mail\Header\MimeVersion;
use Genkgo\Mail\Stream\EmptyStream;
use Genkgo\Mail\Stream\MessageStream;
use Genkgo\Mail\Stream\StringStream;

final class GenericMessage implements MessageInterface
{
    /**
     * @var array<string, array<int, HeaderInterface>>
     */
    private $headers = [
        'return-path' => [],
        'received' => [],
        'dkim-signature' => [],
        'domainkey-signature' => [],
        'sender' => [],
        'message-id' => [],
        'date' => [],
        'subject' => [],
        'from' => [],
        'reply-to' => [],
        'to' => [],
        'cc' => [],
        'bcc' => [],
        'mime-version' => [],
        'content-type' => [],
        'content-transfer-encoding' => [],
    ];

    /**
     * @var StreamInterface
     */
    private $body;
    
    public function __construct()
    {
        $this->body = new EmptyStream();
        $this->headers['mime-version'] = [new MimeVersion()];
    }

    /**
     * @return iterable<iterable<HeaderInterface>>
     */
    public function getHeaders(): iterable
    {
        return $this->headers;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasHeader(string $name): bool
    {
        $name = \strtolower($name);
        return isset($this->headers[$name]) && $this->headers[$name];
    }

    /**
     * @param string $name
     * @return HeaderInterface[]|iterable
     */
    public function getHeader(string $name): iterable
    {
        $name = \strtolower($name);

        if (!isset($this->headers[$name])) {
            return [];
        }

        return $this->headers[$name];
    }

    /**
     * @param HeaderInterface $header
     * @return MessageInterface
     */
    public function withHeader(HeaderInterface $header): MessageInterface
    {
        $clone = clone $this;
        $clone->headers[\strtolower((string)$header->getName())] = [$header];
        return $clone;
    }

    /**
     * @param HeaderInterface $header
     * @return MessageInterface
     */
    public function withAddedHeader(HeaderInterface $header): MessageInterface
    {
        $clone = clone $this;
        $clone->headers[\strtolower((string)$header->getName())][] = $header;
        return $clone;
    }

    /**
     * @param string $name
     * @return MessageInterface
     */
    public function withoutHeader(string $name): MessageInterface
    {
        $clone = clone $this;
        unset($clone->headers[(string)\strtolower($name)]);
        return $clone;
    }

    /**
     * @param StreamInterface $body
     * @return MessageInterface
     */
    public function withBody(StreamInterface $body): MessageInterface
    {
        $clone = clone $this;
        $clone->body = $body;
        return $clone;
    }

    /**
     * @return StreamInterface
     */
    public function getBody(): StreamInterface
    {
        return $this->body;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return (string) (new MessageStream($this));
    }

    /**
     * @param string $messageString
     * @return MessageInterface
     */
    public static function fromString(string $messageString): MessageInterface
    {
        $message = new self();

        $lines = \preg_split('/\r\n/', $messageString);
        if ($lines === false) {
            throw new \UnexpectedValueException('Cannot parse from string, cannot split lines');
        }

        while (($firstKey = \array_key_first($lines)) !== null) {
            $line = $lines[$firstKey];
            unset($lines[$firstKey]);

            if ($line === '') {
                $message = $message->withBody(new StringStream(\implode("\r\n", $lines)));
                break;
            }

            while (($firstKey = \array_key_first($lines)) !== null && isset($lines[$firstKey]) && $lines[$firstKey] !== '' && \trim($lines[$firstKey][0]) === '') {
                $line .= ' ' . \ltrim($lines[$firstKey]);
                unset($lines[$firstKey]);
            }

            $message = $message->withHeader(HeaderLine::fromString($line)->getHeader());
        }

        return $message;
    }
}
