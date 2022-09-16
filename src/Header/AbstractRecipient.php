<?php
declare(strict_types=1);

namespace Genkgo\Mail\Header;

use Genkgo\Mail\Address;
use Genkgo\Mail\AddressList;
use Genkgo\Mail\EmailAddress;
use Genkgo\Mail\HeaderInterface;

abstract class AbstractRecipient implements HeaderInterface
{
    /**
     * @var AddressList
     */
    private $recipients;

    /**
     * @param AddressList $recipients
     */
    final public function __construct(AddressList $recipients)
    {
        $this->recipients = $recipients;
    }

    /**
     * @return HeaderName
     */
    abstract public function getName(): HeaderName;

    /**
     * @return HeaderValue
     */
    final public function getValue(): HeaderValue
    {
        return HeaderValue::fromEncodedString((string)$this->recipients);
    }

    /**
     * @param string $emailAddress
     * @param string $name
     * @return AbstractRecipient
     */
    final public static function fromSingleRecipient(string $emailAddress, string $name = ''): AbstractRecipient
    {
        return new static(
            new AddressList([
                new Address(
                    new EmailAddress($emailAddress),
                    $name
                )
            ])
        );
    }

    /**
     * @param array<array{0: string, 1?: string}> $array pairs of email address and name
     * @return AbstractRecipient
     */
    final public static function fromArray(array $array): AbstractRecipient
    {
        return new static(
            new AddressList(
                \array_map(
                    function (array $pair) {
                        [$emailAddress, $name] = $pair + [1 => ''];
                        return new Address(
                            new EmailAddress($emailAddress),
                            $name
                        );
                    },
                    $array
                )
            )
        );
    }
}
