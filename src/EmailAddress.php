<?php
declare(strict_types=1);

namespace Genkgo\Mail;

/**
 * Class EmailAddress
 * @package Genkgo\Email
 */
final class EmailAddress
{

    /**
     * @var string
     */
    private $address;
    /**
     * @var string
     */
    private $localPart;
    /**
     * @var string
     */
    private $domain;

    /**
     * EmailAddress constructor.
     * @param string $address
     */
    public function __construct(string $address)
    {
        $address = trim($address);

        $hits = preg_match('/^([^@]+)@([^@]+)$/', $address, $matches);
        if ($hits === 0) {
            throw new \InvalidArgumentException('Invalid e-mail address: ' . $address);
        }

        [$this->address, $this->localPart, $this->domain] = $matches;
    }

    /**
     * @return string
     */
    public function getAddress(): string
    {
        return $this->address;
    }

    /**
     * @return string
     */
    public function getLocalPart(): string
    {
        return $this->localPart;
    }

    /**
     * @return string
     */
    public function getDomain(): string
    {
        return $this->domain;
    }

    /**
     * @return string
     */
    public function getPunyCode(): string
    {
        return $this->localPart . '@' . (\idn_to_ascii($this->domain) ?: $this->domain);
    }

    /**
     * @param EmailAddress $address
     * @return bool
     */
    public function equals(EmailAddress $address): bool
    {
        return $this->address === $address->address;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->address;
    }
}
