<?php
declare(strict_types=1);

namespace Genkgo\Mail\Header;

use Genkgo\Mail\Address;
use Genkgo\Mail\AddressList;
use Genkgo\Mail\EmailAddress;
use Genkgo\Mail\HeaderInterface;

/**
 * Class Recipient
 * @package Genkgo\Mail\Header
 */
abstract class AbstractRecipient implements HeaderInterface
{
    /**
     * @var AddressList
     */
    private $recipients;

    /**
     * To constructor.
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
}