<?php
declare(strict_types=1);

namespace Genkgo\Mail\Header;

use Genkgo\Mail\Address;
use Genkgo\Mail\AddressList;
use Genkgo\Mail\HeaderInterface;

final class HeaderLine
{
    /**
     * @var HeaderInterface
     */
    private $header;

    /**
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

        if (\strlen($headerName) > 60) {
            return \sprintf("%s:\r\n %s", $headerName, $headerValue);
        }

        return \sprintf("%s: %s", $headerName, $headerValue);
    }

    /**
     * @param string $line
     * @return HeaderLine
     */
    public static function fromString(string $line): HeaderLine
    {
        $parts = \preg_split('/\s*\:\s*/', $line, 2);

        if ($parts === false || \count($parts) !== 2) {
            throw new \InvalidArgumentException('Invalid header line');
        }

        [$name, $value] = $parts;

        $header = match (\strtolower($name)) {
            'from' => new From(Address::fromString($value)),
            'to' => new To(AddressList::fromString($value)),
            'cc' => new Cc(AddressList::fromString($value)),
            'bcc' => new Bcc(AddressList::fromString($value)),
            'reply-to' => new ReplyTo(AddressList::fromString($value)),
            default => new ParsedHeader(new HeaderName($name), HeaderValue::parse($value))
        };

        return new self($header);
    }
}
