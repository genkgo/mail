<?php
declare(strict_types=1);

namespace Genkgo\Mail\Header;

use Genkgo\Mail\HeaderInterface;

final class MessageId implements HeaderInterface
{
    /**
     * @var string
     */
    private $leftHand;
    /**
     * @var string
     */
    private $rightHand;

    /**
     * MessageId constructor.
     * @param string $leftHand
     * @param string $rightHand
     */
    public function __construct(string $leftHand, string $rightHand)
    {
        $this->leftHand = $leftHand;
        $this->rightHand = $rightHand;
    }

    /**
     * @return HeaderName
     */
    public function getName(): HeaderName
    {
        return new HeaderName('Message-ID');
    }

    /**
     * @return HeaderValue
     */
    public function getValue(): HeaderValue
    {
        return new HeaderValue(sprintf('<%s@%s>', $this->leftHand, $this->rightHand));
    }

    /**
     * @param string $rightHand
     * @return MessageId
     */
    public static function newRandom(string $rightHand): MessageId
    {
        return new self(bin2hex(random_bytes(8)), $rightHand);
    }
}
