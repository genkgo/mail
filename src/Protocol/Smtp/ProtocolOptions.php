<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Smtp;

final class ProtocolOptions
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
     * @return ProtocolOptions
     */
    public function withTimeout(float $connectionTimeout): ProtocolOptions
    {
        $clone = clone $this;
        $clone->timeout = $connectionTimeout;
        return $clone;
    }

    /**
     * @param string $username
     * @return ProtocolOptions
     */
    public function withUsername(string $username): ProtocolOptions
    {
        $clone = clone $this;
        $clone->username = $username;
        return $clone;
    }

    /**
     * @param string $password
     * @return ProtocolOptions
     */
    public function withPassword(string $password): ProtocolOptions
    {
        $clone = clone $this;
        $clone->password = $password;
        return $clone;
    }

    /**
     * @param string $ehlo
     * @return ProtocolOptions
     */
    public function withEhlo(string $ehlo): ProtocolOptions
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


}