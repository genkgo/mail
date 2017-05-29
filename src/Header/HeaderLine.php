<?php
declare(strict_types=1);

namespace Genkgo\Mail\Header;

use Genkgo\Mail\HeaderInterface;

final class HeaderLine
{
    /**
     * @var HeaderInterface
     */
    private $header;

    /**
     * HeaderLine constructor.
     * @param HeaderInterface $header
     */
    public function __construct(HeaderInterface $header)
    {
        $this->header = $header;
    }

    /**
     * @return HeaderInterface
     */
    public function getHeader(): HeaderInterface
    {
        return $this->header;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        $headerName = (string)$this->header->getName();
        $headerValue = (string)$this->header->getValue();

        $firstFoldingAt = strpos($headerValue, "\r\n");
        if ($firstFoldingAt === false) {
            $firstFoldingAt = strlen($headerValue);
        }

        if (strlen($headerName) + $firstFoldingAt > 76) {
            return sprintf("%s:\r\n %s", $headerName, $headerValue);
        }

        return sprintf("%s: %s", $headerName, $headerValue);
    }

    /**
     * @param string $line
     * @return HeaderLine
     */
    public static function fromString(string $line): HeaderLine
    {
        [$name, $value] = preg_split('/\:\s*/', $line, 2);

        if (substr($value, 0, 2) === '=?' && substr($value, -2, 2) === '?=') {
            $value = iconv_mime_decode($value);
        }

        return new self(new GenericHeader($name, $value));
    }

}