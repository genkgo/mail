<?php
declare(strict_types=1);

namespace Genkgo\Mail\Header;

use Genkgo\Mail\HeaderInterface;

final class ContentType implements HeaderInterface
{
    private const RFC_1341_ADDITIONAL_SPECIALS = "\x2F\x3F\x3D";

    /**
     * @var string
     */
    private $contentType;

    /**
     * @var string
     */
    private $charset;

    /**
     * @param string $contentType
     * @param string $charset
     */
    public function __construct(string $contentType, string $charset = '')
    {
        $this->contentType = $contentType;

        if ($charset === '' && \substr($contentType, 0, 5) === 'text/') {
            $charset = 'UTF-8';
        }

        $this->charset = $charset;
    }

    /**
     * @return HeaderName
     */
    public function getName(): HeaderName
    {
        return new HeaderName('Content-Type');
    }

    /**
     * @return HeaderValue
     */
    public function getValue(): HeaderValue
    {
        if ($this->charset === '') {
            return new HeaderValue($this->contentType);
        }

        $charset = (new HeaderValueParameter(
            'charset',
            $this->charset
        ))
            ->withSpecials(HeaderValueParameter::RFC_822_T_SPECIAL . self::RFC_1341_ADDITIONAL_SPECIALS);

        return (new HeaderValue($this->contentType))->withParameter($charset);
    }

    /**
     * @return ContentType
     */
    public static function unknown(): ContentType
    {
        return new self('application/octet-stream', '');
    }
}
