<?php
declare(strict_types=1);

namespace Genkgo\Mail;

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
     * @param string $address
     */
    public function __construct(string $address)
    {
        if (\preg_match('/\v/u', $address, $matches) !== 0) {
            throw new \InvalidArgumentException('Cannot use vertical white space within email address');
        }

        $address = \trim($address);

        $hits = \preg_match('/^([^@]+)@([^@]+)$/', $address, $matches);
        if ($hits === 0) {
            throw new \InvalidArgumentException('Invalid e-mail address: ' . $address);
        }

        /** @var array{0: non-empty-string, 1: non-empty-string, 2: non-empty-string} $matches */
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
        return \sprintf('%s@%s', $this->getPunyCodeLocalPart(), $this->getPunyCodeDomain());
    }

    /**
     * @return string
     */
    public function getPunyCodeLocalPart(): string
    {
        return \idn_to_ascii($this->localPart, 0, INTL_IDNA_VARIANT_UTS46) ?: $this->localPart;
    }

    /**
     * @return string
     */
    public function getPunyCodeDomain(): string
    {
        return \idn_to_ascii($this->domain, 0, INTL_IDNA_VARIANT_UTS46) ?: $this->domain;
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
