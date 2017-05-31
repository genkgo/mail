<?php
declare(strict_types=1);

namespace Genkgo\Mail\Transport;

final class SmtpTransportOptions
{
    /**
     * @var float
     */
    private $timeout = 1;
    /**
     * @var string
     */
    private $username = '';
    /**
     * @var string
     */
    private $password = '';
    /**
     * @var string
     */
    private $ehlo = '127.0.0.1';

    /**
     * @param float $connectionTimeout
     * @return SmtpTransportOptions
     */
    public function withTimeout(float $connectionTimeout): SmtpTransportOptions
    {
        $clone = clone $this;
        $clone->timeout = $connectionTimeout;
        return $clone;
    }

    /**
     * @param string $username
     * @return SmtpTransportOptions
     */
    public function withUsername(string $username): SmtpTransportOptions
    {
        $clone = clone $this;
        $clone->username = $username;
        return $clone;
    }

    /**
     * @param string $password
     * @return SmtpTransportOptions
     */
    public function withPassword(string $password): SmtpTransportOptions
    {
        $clone = clone $this;
        $clone->password = $password;
        return $clone;
    }

    /**
     * @param string $ehlo
     * @return SmtpTransportOptions
     */
    public function withEhlo(string $ehlo): SmtpTransportOptions
    {
        $clone = clone $this;
        $clone->ehlo = $ehlo;
        return $clone;
    }

    /**
     * @return float
     */
    public function getTimeout(): float
    {
        return $this->timeout;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @return string
     */
    public function getEhlo(): string
    {
        return $this->ehlo;
    }

    /**
     * @return bool
     */
    public function requiresLogin(): bool
    {
        return $this->username !== '' && $this->password !== '';
    }

    /**
     * @return \DateInterval
     */
    public function getMaxConnectionDuration()
    {
        return new \DateInterval('P5M');
    }
}