<?php
declare(strict_types=1);

namespace Genkgo\Mail;

final class AddressList implements \Countable, \IteratorAggregate
{
    private const PARSE_START = 1;
    
    private const PARSE_QUOTE = 2;

    /**
     * @var array|Address[]
     */
    private $addresses = [];

    /**
     * @param array|Address[] $recipients
     */
    public function __construct(array $recipients = [])
    {
        foreach ($recipients as $recipient) {
            if ($recipient instanceof Address === false) {
                throw new \InvalidArgumentException('Recipient must be Address object');
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
     * @param AddressList $addressList
     * @return AddressList
     */
    public function withList(AddressList $addressList): AddressList
    {
        $clone = clone $this;
        $clone->addresses = \array_merge($this->addresses, $addressList->addresses);
        return $clone;
    }

    /**
     * @return Address
     */
    public function first(): Address
    {
        if (empty($this->addresses)) {
            throw new \OutOfRangeException();
        }

        return \reset($this->addresses);
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return \count($this->addresses);
    }

    /**
     * @return \ArrayIterator|Address[]
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->addresses);
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return \implode(
            ",\r\n ",
            \array_map(
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
        $addressListAsString = \trim($addressListAsString);
        if ($addressListAsString === '') {
            return new self([]);
        }

        $addresses = [];
        $length = \strlen($addressListAsString) - 1;
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
                        $addresses[] = Address::fromString(\substr($sequence, 0, -1));
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
