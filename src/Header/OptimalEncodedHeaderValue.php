<?php
declare(strict_types=1);

namespace Genkgo\Mail\Header;

use Genkgo\Mail\Stream\OptimalTransferEncodedPhraseStream;
use Genkgo\Mail\Stream\OptimalTransferEncodedTextStream;

final class OptimalEncodedHeaderValue
{
    private const FOLDING = "\r\n ";

    /**
     * @var string
     */
    private $encoded;

    /**
     * @var string
     */
    private $encoding = '7bit';

    /**
     * @param string $value
     * @param bool $phrase
     */
    public function __construct(string $value, bool $phrase = false)
    {
        if ($phrase === true) {
            $encoded = new OptimalTransferEncodedPhraseStream($value, 68, self::FOLDING);

            $this->encoding = $encoded->getMetadata(['transfer-encoding'])['transfer-encoding'];
        } else {
            $encoded = new OptimalTransferEncodedTextStream($value, 68, self::FOLDING);

            $this->encoding = $encoded->getMetadata(['transfer-encoding'])['transfer-encoding'];
        }

        switch ($this->encoding) {
            case '7bit':
            case '8bit':
                $this->encoded = (string) $encoded;
                break;
            case 'base64':
                $this->encoded = \sprintf('=?%s?B?%s?=', 'UTF-8', (string) $encoded);
                break;
            case 'quoted-printable':
                $this->encoded = \sprintf('=?%s?Q?%s?=', 'UTF-8', (string) $encoded);
                break;
            default:
                throw new \UnexpectedValueException('Unknown encoding ' . $this->encoding);
        }
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->encoded;
    }

    /**
     * @return string
     */
    public function getEncoding(): string
    {
        return $this->encoding;
    }

    /**
     * @param string $value
     * @return OptimalEncodedHeaderValue
     */
    public static function forPhrase(string $value): self
    {
        return new self($value, true);
    }
}
