<?php
declare(strict_types=1);

namespace Genkgo\Mail\Transport;

use Genkgo\Mail\AddressList;
use Genkgo\Mail\EmailAddress;
use Genkgo\Mail\MessageInterface;

/**
 * Class EnvelopOptions
 * @package Genkgo\Mail\Transport
 */
final class EnvelopeFactory
{
    /**
     * @var \Closure
     */
    private $callback;
    /**
     * @var EmailAddress
     */
    private $fallback;

    /**
     * EnvelopOptions constructor.
     */
    private function __construct()
    {
    }

    /**
     * @param EmailAddress $emailAddress
     * @return EnvelopeFactory
     */
    public function withFallback(EmailAddress $emailAddress): EnvelopeFactory
    {
        $clone = clone $this;
        $clone->fallback = $emailAddress;
        return $clone;
    }

    /**
     * @param MessageInterface $message
     * @return EmailAddress
     */
    public function make(MessageInterface $message): EmailAddress
    {
        try {
            return $this->callback->call($this, $message);
        } catch (\RuntimeException $e) {
            if ($this->fallback === null) {
                throw $e;
            }
        }

        return $this->fallback;
    }

    /**
     * @param MessageInterface $message
     * @return EmailAddress
     */
    private function extractHeader(MessageInterface $message): EmailAddress
    {
        foreach (['sender', 'from'] as $header) {
            if ($message->hasHeader($header)) {
                try {
                    return $this->extractFromAddressListHeader($message, $header);
                } catch (\OutOfRangeException $e) {
                }
            }
        }

        throw new \RuntimeException('Cannot extract envelope from headers');
    }

    /**
     * @param MessageInterface $message
     * @param string $headerName
     * @return EmailAddress
     */
    private function extractFromAddressListHeader (MessageInterface $message, string $headerName): EmailAddress
    {
        return AddressList::fromString(
            (string)$message->getHeader($headerName)[0]->getValue()->getRaw()
        )->first()->getAddress();
    }

    /**
     * @return EnvelopeFactory
     */
    public static function useExtractedHeader()
    {
        $options = new self();
        $options->callback = \Closure::fromCallable([$options, 'extractHeader']);
        return $options;
    }

    /**
     * @param EmailAddress $fixedAddress
     * @return EnvelopeFactory
     */
    public static function useFixed(EmailAddress $fixedAddress)
    {
        $options = new self();
        $options->callback = function () use ($fixedAddress) {
            return $fixedAddress;
        };
        return $options;
    }

    /**
     * @param \Closure $callback
     * @return EnvelopeFactory
     */
    public static function useCallback(\Closure $callback)
    {
        $options = new self();
        $options->callback = $callback;
        return $options;
    }

}