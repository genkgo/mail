<?php
declare(strict_types=1);

namespace Genkgo\Mail\Header;

use Genkgo\Mail\HeaderInterface;

final class MessageId implements HeaderInterface
{
    /**
     * @var string
     */
    private $localPart;

    /**
     * @var string
     */
    private $domain;

    /**
     * @param string $localPart
     * @param string $domain
     */
    public function __construct(string $localPart, string $domain)
    {
        $this->localPart = $localPart;
        $this->domain = $domain;
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
        return new HeaderValue(
            \sprintf(
                '<%s@%s>',
                $this->localPart,
                \idn_to_ascii($this->domain, 0, INTL_IDNA_VARIANT_UTS46) ?: $this->domain
            )
        );
    }

    /**
     * @param string $rightHand
     * @return MessageId
     */
    public static function newRandom(string $rightHand): MessageId
    {
        return new self(\bin2hex(\random_bytes(8)), $rightHand);
    }
}
