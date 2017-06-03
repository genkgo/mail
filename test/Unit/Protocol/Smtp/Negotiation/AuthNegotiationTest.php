<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Protocol\Smtp;

use Genkgo\Mail\Exception\SmtpAuthenticationException;
use Genkgo\Mail\Protocol\ConnectionInterface;
use Genkgo\Mail\Protocol\Smtp\Client;
use Genkgo\Mail\Protocol\Smtp\Negotiation\AuthNegotiation;
use Genkgo\TestMail\AbstractTestCase;

final class AuthNegotiationTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_uses_advertised()
    {
        $connection = $this->createMock(ConnectionInterface::class);
        $connection
            ->expects($this->at(0))
            ->method('addListener');

        $connection
            ->expects($this->at(1))
            ->method('send')
            ->with("EHLO hostname\r\n");

        $connection
            ->expects($this->at(2))
            ->method('receive')
            ->willReturn("250-hello\r\n");

        $connection
            ->expects($this->at(3))
            ->method('receive')
            ->willReturn("250 AUTH LOGIN\r\n");

        $connection
            ->expects($this->at(4))
            ->method('send')
            ->with("AUTH LOGIN\r\n");

        $connection
            ->expects($this->at(5))
            ->method('receive')
            ->willReturn("330 Send it\r\n");

        $connection
            ->expects($this->at(6))
            ->method('send')
            ->with(base64_encode('user') . "\r\n");

        $connection
            ->expects($this->at(7))
            ->method('receive')
            ->willReturn("330 Send it\r\n");

        $connection
            ->expects($this->at(8))
            ->method('send')
            ->with(base64_encode('pass') . "\r\n");

        $connection
            ->expects($this->at(9))
            ->method('receive')
            ->willReturn("220 OK\r\n");

        $negotiator = new AuthNegotiation('hostname', Client::AUTH_AUTO, 'user', 'pass');
        $negotiator->negotiate(new Client($connection));
    }

    /**
     * @test
     */
    public function it_uses_login()
    {
        $connection = $this->createMock(ConnectionInterface::class);
        $connection
            ->expects($this->at(0))
            ->method('addListener');

        $connection
            ->expects($this->at(1))
            ->method('send')
            ->with("AUTH LOGIN\r\n");

        $connection
            ->expects($this->at(2))
            ->method('receive')
            ->willReturn("330 Send it\r\n");

        $connection
            ->expects($this->at(3))
            ->method('send')
            ->with(base64_encode('user') . "\r\n");

        $connection
            ->expects($this->at(4))
            ->method('receive')
            ->willReturn("330 Send it\r\n");

        $connection
            ->expects($this->at(5))
            ->method('send')
            ->with(base64_encode('pass') . "\r\n");

        $connection
            ->expects($this->at(6))
            ->method('receive')
            ->willReturn("220 OK\r\n");

        $negotiator = new AuthNegotiation('hostname', Client::AUTH_LOGIN, 'user', 'pass');
        $negotiator->negotiate(new Client($connection));
    }

    /**
     * @test
     */
    public function it_uses_plain()
    {
        $connection = $this->createMock(ConnectionInterface::class);
        $connection
            ->expects($this->at(0))
            ->method('addListener');

        $connection
            ->expects($this->at(1))
            ->method('send')
            ->with("AUTH PLAIN\r\n");

        $connection
            ->expects($this->at(2))
            ->method('receive')
            ->willReturn("330 Send it\r\n");

        $connection
            ->expects($this->at(3))
            ->method('send')
            ->with(base64_encode("\0user\0pass") . "\r\n");

        $connection
            ->expects($this->at(4))
            ->method('receive')
            ->willReturn("220 OK\r\n");

        $negotiator = new AuthNegotiation('hostname', Client::AUTH_PLAIN, 'user', 'pass');
        $negotiator->negotiate(new Client($connection));
    }

    /**
     * @test
     */
    public function it_will_throw_when_not_advertised()
    {
        $this->expectException(SmtpAuthenticationException::class);

        $connection = $this->createMock(ConnectionInterface::class);
        $connection
            ->expects($this->at(0))
            ->method('addListener');

        $connection
            ->expects($this->at(1))
            ->method('send')
            ->with("EHLO hostname\r\n");

        $connection
            ->expects($this->at(2))
            ->method('receive')
            ->willReturn("250-hello\r\n");

        $connection
            ->expects($this->at(3))
            ->method('receive')
            ->willReturn("250 AUTH XXX\r\n");

        $negotiator = new AuthNegotiation('hostname', Client::AUTH_AUTO, 'user', 'pass');
        $negotiator->negotiate(new Client($connection));
    }
}