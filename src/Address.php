<?php
declare(strict_types=1);

namespace Genkgo\Mail;

use Genkgo\Mail\Header\HeaderValue;

final class Address
{

    /**
     * @var EmailAddress
     */
    private $address;

    /**
     * @var string
     */
    private $name;

    /**
     * To constructor.
     * @param EmailAddress $address
     * @param string $name
     */
    public function __construct(EmailAddress $address, string $name = '')
    {
        if (preg_match('/\v/', $name) > 0) {
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
     * @return string
     */
    public function __toString(): string
    {
        if ($this->name === '') {
            return (string)$this->address;
        }

        $name = $this->name;

        $encodedName = addcslashes($name, "\0..\37\177\\\"");

        if ($encodedName !== $name || preg_match('/[^A-Za-z0-9!#$%&\'*+\/=?^_`{|}~ -]/', $this->name) === 1) {
            $encodedName = sprintf('"%s"', $encodedName);
        }

        return (string)(new HeaderValue($encodedName)) . ' <' . $this->address->getPunyCode() . '>';
    }
}