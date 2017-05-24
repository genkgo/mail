<?php
declare(strict_types=1);

namespace Genkgo\Mail\Header;

use Genkgo\Mail\Address;
use Genkgo\Mail\HeaderInterface;

/**
 * Class Recipient
 * @package Genkgo\Email\Header
 */
abstract class AbstractRecipient implements HeaderInterface
{
    /**
     * @var array|Address[]
     */
    private $recipients = [];

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

            $this->recipients[] = $recipient;
        }
    }

    /**
     * @param Address $recipient
     * @return AbstractRecipient
     */
    public function withRecipient(Address $recipient): AbstractRecipient
    {
        $clone = $this;
        $clone->recipients[] = $recipient;
        return $clone;
    }

    /**
     * @return HeaderName
     */
    abstract public function getName(): HeaderName;

    /**
     * @return HeaderValue
     */
    public function getValue(): HeaderValue
    {
        return new HeaderValue(
            implode(
                ',',
                array_map(
                    function (Address $addressAndName) {
                        return (string) $addressAndName;
                    },
                    $this->recipients
                )
            )
        );
    }
}