<?php
declare(strict_types=1);

namespace Genkgo\Mail;

interface MessageInterface
{
    /**
     * @return iterable
     */
    public function getHeaders(): iterable;

    /**
     * @param string $name
     * @return bool
     */
    public function hasHeader(string $name): bool;

    /**
     * @param string $name
     * @return iterable|HeaderInterface[]
     */
    public function getHeader(string $name): iterable;

    /**
     * @param HeaderInterface $header
     * @return MessageInterface
     */
    public function withHeader(HeaderInterface $header): MessageInterface;

    /**
     * @param HeaderInterface $header
     * @return MessageInterface
     */
    public function withAddedHeader(HeaderInterface $header): MessageInterface;

    /**
     * @param string $name
     * @return MessageInterface
     */
    public function withoutHeader(string $name): MessageInterface;

    /**
     * @return StreamInterface
     */
    public function getBody(): StreamInterface ;

    /**
     * @param StreamInterface $body
     * @return MessageInterface
     */
    public function withBody(StreamInterface $body): MessageInterface;

    /**
     * @return string
     */
    public function __toString(): string;
}
