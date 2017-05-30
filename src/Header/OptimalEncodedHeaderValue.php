<?php
declare(strict_types=1);

namespace Genkgo\Mail\Header;

use Genkgo\Mail\Stream\OptimalTransferEncodedTextStream;

/**
 * Class OptimalEncodedHeaderValue
 * @package Genkgo\Mail\Header
 */
final class OptimalEncodedHeaderValue
{
    /**
     *
     */
    private const FOLDING = "\r\n ";
    /**
     * @var string
     */
    private $value;

    /**
     * OptimalEncodedHeaderValue constructor.
     * @param string $value
     */
    public function __construct(string $value)
    {
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        $encoded = new OptimalTransferEncodedTextStream($this->value, 68, self::FOLDING);

        $encoding = $encoded->getMetadata(['transfer-encoding'])['transfer-encoding'];
        if ($encoding === '7bit' || $encoding === '8bit') {
            return (string) $encoded;
        }

        if ($encoding === 'base64') {
            return sprintf('=?%s?B?%s?=', 'UTF-8', (string) $encoded);
        }

        return sprintf('=?%s?Q?%s?=', 'UTF-8', (string) $encoded);
    }

}