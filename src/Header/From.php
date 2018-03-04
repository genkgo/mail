<?php
declare(strict_types=1);

namespace Genkgo\Mail\Header;

use Genkgo\Mail\Address;
use Genkgo\Mail\EmailAddress;
use Genkgo\Mail\HeaderInterface;

final class From implements HeaderInterface
{
    /**
     * @var Address
     */
    private $from;

    /**
     * @param Address $from
     */
    public function __construct(Address $from)
    {
        $this->from = $from;
    }

    /**
     * @return HeaderName
     */
    public function getName(): HeaderName
    {
        return new HeaderName('From');
    }

    /**
     * @return HeaderValue
     */
    public function getValue(): HeaderValue
    {
        return new HeaderValue((string)$this->from);
    }

    /**
     * @param string $emailAddress
     * @param string $name
     * @return From
     */
    public static function fromAddress(string $emailAddress, string $name = ''): From
    {
        return new self(new Address(new EmailAddress($emailAddress), $name));
    }

    /**
     * @param string $emailAddress
     * @return From
     */
    public static function fromEmailAddress(string $emailAddress): From
    {
        return new self(new Address(new EmailAddress($emailAddress)));
    }
}
