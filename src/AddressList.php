<?php
declare(strict_types=1);

namespace Genkgo\Mail;

/**
 * Class AddressList
 * @package Genkgo\Mail
 */
final class AddressList implements \Countable
{
    /**
     *
     */
    private CONST PARSE_START = 1;
    /**
     *
     */
    private CONST PARSE_QUOTE = 2;

    /**
     * @var array|Address[]
     */
    private $addresses = [];

    /**
     * To constructor.
     * @param array|Address[] $recipients
     */
    public function __construct(array $recipients = [])
    {
        foreach ($recipients as $recipient) {
            if ($recipient instanceof Address === false) {
                throw new \InvalidArgumentException('Recipient must be EmailAddressAndName object');
            }

            $this->addresses[] = $recipient;
        }
    }

    /**
     * @param Address $address
     * @return AddressList
     */
    public function withAddress(Address $address): AddressList
    {
        $clone = clone $this;
        $clone->addresses[] = $address;
        return $clone;
    }

    /**
     * @param Address $address
     * @return AddressList
     */
    public function withoutAddress(Address $address): AddressList
    {
        $clone = clone $this;

        foreach ($this->addresses as $key => $mayRemoveAddress) {
            if ($mayRemoveAddress->equals($address)) {
                unset($clone->addresses[$key]);
            }
        }

        return $clone;
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->addresses);
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return implode(
            ',',
            array_map(
                function (Address $addressAndName) {
                    return (string) $addressAndName;
                },
                $this->addresses
            )
        );
    }

    /**
     * @param string $addressListAsString
     * @return AddressList
     */
    public static function fromString(string $addressListAsString)
    {
        $addressListAsString = trim($addressListAsString);
        if ($addressListAsString === '') {
            return new self([]);
        }

        $addresses = [];
        $length = strlen($addressListAsString) - 1;
        $n = -1;
        $state = self::PARSE_START;
        $escapeNext = false;
        $sequence = '';

        while ($n < $length) {
            $n++;

            $char = $addressListAsString[$n];

            $sequence .= $char;

            if ($char === '\\') {
                $escapeNext = true;
                continue;
            }

            if ($escapeNext) {
                $escapeNext = false;
                continue;
            }

            switch ($state) {
                case self::PARSE_QUOTE:
                    if ($char === '"') {
                        $state = self::PARSE_START;
                    }

                    break;
                default:
                    if ($char === '"') {
                        $state = self::PARSE_QUOTE;
                    }

                    if ($char === ',') {
                        $addresses[] = Address::fromString(substr($sequence, 0, -1));
                        $sequence = '';
                        $state = self::PARSE_START;
                    }
                    break;
            }
        }

        $addresses[] = Address::fromString($sequence);

        return new self($addresses);
    }
}