<?php
declare(strict_types=1);

namespace Genkgo\Mail;

use Genkgo\Mail\Header\OptimalEncodedHeaderValue;

final class Address
{
    private const PARSE_POSITION_START = 1;
    
    private const PARSE_POSITION_QUOTE = 2;
    
    private const PARSE_STATE_EMAIL = 1;
    
    private const PARSE_STATE_TAGGED_EMAIL = 2;

    /**
     * @var EmailAddress
     */
    private $address;

    /**
     * @var string
     */
    private $name;

    /**
     * @param EmailAddress $address
     * @param string $name
     */
    public function __construct(EmailAddress $address, string $name = '')
    {
        if (\preg_match('/\v/', $name) !== 0) {
            throw new \InvalidArgumentException('Cannot use vertical white space within name of email address');
        }

        $this->address = $address;
        $this->name = $name;
    }

    /**
     * @return EmailAddress
     */
    public function getAddress(): EmailAddress
    {
        return $this->address;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param Address $address
     * @return bool
     */
    public function equals(Address $address): bool
    {
        return $this->address->equals($address->address) && $this->name === $address->name;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        if ($this->name === '') {
            return (string)$this->address;
        }

        $encodedName = (string) OptimalEncodedHeaderValue::forPhrase($this->name);
        if ($encodedName === $this->name) {
            $encodedName = \addcslashes($encodedName, "\0..\37\177\\\"");

            if ($encodedName !== $this->name || \preg_match('/[^A-Za-z0-9!#$%&\'*+\/=?^_`{|}~ -]/', $this->name) === 1) {
                $encodedName = \sprintf('"%s"', $encodedName);
            }
        }

        return \sprintf('%s <%s>', $encodedName, $this->address->getPunyCode());
    }

    /**
     * @return string
     */
    public function toReadableString(): string
    {
        if ($this->name === '') {
            return (string)$this->address;
        }

        $encodedName = $this->name;
        if ($encodedName !== $this->name || \preg_match('/[^A-Za-z0-9!#$%&\'*+\/=?^_`{|}~ -]/', $this->name) === 1) {
            $encodedName = \sprintf('"%s"', $encodedName);
        }

        return \sprintf('%s <%s>', $encodedName, $this->address->getAddress());
    }

    /**
     * @param string $addressAsString
     * @return Address
     */
    public static function fromString(string $addressAsString)
    {
        $addressAsString = \trim($addressAsString);

        if ($addressAsString === '') {
            throw new \InvalidArgumentException('Address cannot be empty');
        }

        $sequence = '';
        $length = \strlen($addressAsString) - 1;
        $n = -1;
        $state = self::PARSE_STATE_EMAIL;
        $position = self::PARSE_POSITION_START;
        $escapeNext = false;
        $name = '';
        $email = '';
        $nameQuoted = false;

        while ($n < $length) {
            $n++;

            $char = $addressAsString[$n];

            if ($char === '\\') {
                $escapeNext = true;
                continue;
            }

            $sequence .= $char;

            if ($escapeNext) {
                $escapeNext = false;
                continue;
            }

            switch ($position) {
                case self::PARSE_POSITION_QUOTE:
                    if ($char === '"') {
                        $position = self::PARSE_POSITION_START;
                    }

                    break;
                default:
                    if ($char === '"') {
                        $position = self::PARSE_POSITION_QUOTE;
                    }

                    if ($char === '"' && $state === self::PARSE_STATE_EMAIL) {
                        $nameQuoted = true;
                    }
                    break;
            }

            switch ($state) {
                case self::PARSE_STATE_TAGGED_EMAIL:
                    if ($position !== self::PARSE_POSITION_QUOTE && $char === '>') {
                        $state = self::PARSE_STATE_EMAIL;
                        $email = \substr($sequence, 0, -1);
                    }

                    break;
                default:
                    if ($email !== '') {
                        throw new \InvalidArgumentException('Invalid characters used after <>');
                    }

                    if ($position !== self::PARSE_POSITION_QUOTE && $char === '<') {
                        $state = self::PARSE_STATE_TAGGED_EMAIL;
                        $name = \trim(\substr($sequence, 0, -1));
                        $sequence = '';
                    }
                    break;
            }
        }

        if ($position === self::PARSE_POSITION_QUOTE) {
            throw new \InvalidArgumentException('Address uses starting quotes but no ending quotes');
        }

        if ($state === self::PARSE_STATE_TAGGED_EMAIL) {
            throw new \InvalidArgumentException('Address uses starting tag (<) but no ending tag (>)');
        }

        if ($name === '' && $email === '') {
            return new self(new EmailAddress($sequence));
        }

        if ($nameQuoted && $name[0] !== '"') {
            throw new \InvalidArgumentException('Invalid characters before "');
        }

        if ($nameQuoted) {
            $name = \substr($name, 1, -1);
        }

        $name = @\iconv_mime_decode($name);
        if ($name === false) {
            throw new \InvalidArgumentException('Failed to mime decode the name');
        }

        return new self(new EmailAddress($email), $name);
    }
}
