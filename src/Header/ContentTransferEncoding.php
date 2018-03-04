<?php
declare(strict_types=1);

namespace Genkgo\Mail\Header;

use Genkgo\Mail\HeaderInterface;

final class ContentTransferEncoding implements HeaderInterface
{
    private const VALID_ENCODINGS = [
        '7bit' => true,
        '8bit' => true,
        'binary' => true,
        'quoted-printable' => true,
        'base64' => true,
        'ietf-token' => true,
        'x-token' => true,
    ];

    /**
     * @var string
     */
    private $encoding;

    /**
     * @param string $encoding
     */
    public function __construct($encoding)
    {
        if (!isset(self::VALID_ENCODINGS[$encoding])) {
            throw new \InvalidArgumentException('Invalid Content-Transfer-Encoding ' . $encoding);
        }

        $this->encoding = $encoding;
    }

    /**
     * @return HeaderName
     */
    public function getName(): HeaderName
    {
        return new HeaderName('Content-Transfer-Encoding');
    }

    /**
     * @return HeaderValue
     */
    public function getValue(): HeaderValue
    {
        return new HeaderValue($this->encoding);
    }
}
