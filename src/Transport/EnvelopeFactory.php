<?php
declare(strict_types=1);

namespace Genkgo\Mail\Transport;

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
     * EnvelopOptions constructor.
     */
    private function __construct()
    {
    }

    /**
     * @param MessageInterface $message
     * @return EmailAddress
     */
    public function make(MessageInterface $message): EmailAddress
    {
        return $this->callback->call($this, $message);
    }

    /**
     * @return EnvelopeFactory
     */
    public static function useExtractedFromHeader()
    {
        $options = new self();
        $options->callback = function (MessageInterface $message) {
            $fromHeader = $message->getHeader('from');

            if (empty($fromHeader)) {
                throw new \InvalidArgumentException(
                    'Cannot determine envelop from message with empty from header'
                );
            }

            $headerValue = (string)reset($fromHeader)->getValue();
            $tagStart = strpos($headerValue, '<');
            $tagEnd = strpos($headerValue, '>');

            if ($tagStart === false && $tagEnd === false) {
                return new EmailAddress($headerValue);
            }

            return new EmailAddress(substr($headerValue, $tagStart + 1, $tagEnd - $tagStart - 1));
        };
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