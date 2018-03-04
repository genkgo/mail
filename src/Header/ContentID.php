<?php
declare(strict_types=1);

namespace Genkgo\Mail\Header;

use Genkgo\Mail\HeaderInterface;

final class ContentID implements HeaderInterface
{
    /**
     * @var string
     */
    private $contentID;

    /**
     * @param string $contentID
     */
    public function __construct(string $contentID)
    {
        $this->contentID = $contentID;
    }

    /**
     * @return HeaderName
     */
    public function getName(): HeaderName
    {
        return new HeaderName('Content-ID');
    }

    /**
     * @return HeaderValue
     */
    public function getValue(): HeaderValue
    {
        return new HeaderValue($this->contentID);
    }

    /**
     * @param string $localPart
     * @param string $domain
     * @return ContentID
     */
    public static function fromUrlAddress(string $localPart, string $domain): self
    {
        return new self(
            \sprintf(
                '<%s@%s>',
                $localPart,
                \idn_to_ascii($domain, 0, INTL_IDNA_VARIANT_UTS46) ?: $domain
            )
        );
    }
}
