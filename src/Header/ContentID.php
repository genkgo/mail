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
     * From constructor.
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
}
