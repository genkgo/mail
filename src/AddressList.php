<?php
declare(strict_types=1);

namespace Genkgo\Mail;

/**
 * Class AddressList
 * @package Genkgo\Mail
 */
final class AddressList
{

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
}