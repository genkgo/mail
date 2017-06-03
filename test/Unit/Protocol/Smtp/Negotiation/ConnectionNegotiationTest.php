<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Protocol\Smtp;

use Genkgo\Mail\Exception\ConnectionInsecureException;
use Genkgo\Mail\Protocol\ConnectionInterface;
use Genkgo\Mail\Protocol\Smtp\Client;
use Genkgo\Mail\Protocol\Smtp\Negotiation\ConnectionNegotiation;
use Genkgo\TestMail\AbstractTestCase;

final class ConnectionNegotiationTest extends AbstractTestCase
{

    /**
     * @test
     */
    public function it_sends_ehlo()
    {
        $connection = $this->createMock(ConnectionInterface::class);
        $connection
            ->expects($this->at(0))
            ->method('addListener');

        $connection
            ->expects($this->at(1))
            ->method('receive')
            ->willReturn('Welcome');

        $connection
            ->expects($this->at(2))
            ->method('send')
            ->with("EHLO hostname\r\n");

        $connection
            ->expects($this->at(3))
            ->method('receive')
            ->willReturn("250 hello\r\n");

        $negotiator = new ConnectionNegotiation($connection, 'hostname', true);
        $negotiator->negotiate(new Client($connection));
    }

    /**
     * @test
     */
    public function it_throw_when_not_secure()
    {
        $this->expectException(ConnectionInsecureException::class);

        $connection = $this->createMock(ConnectionInterface::class);
        $connection
            ->expects($this->at(0))
            ->method('addListener');

        $connection
            ->expects($this->at(1))
            ->method('receive')
            ->willReturn('Welcome');

        $connection
            ->expects($this->at(2))
            ->method('send')
            ->with("EHLO hostname\r\n");

        $connection
            ->expects($this->at(3))
            ->method('receive')
            ->willReturn("250 hello\r\n");

        $negotiator = new ConnectionNegotiation($connection, 'hostname', false);
        $negotiator->negotiate(new Client($connection));
    }

    /**
     * @test
     */
    public function it_sends_starttls_when_advertised()
    {
        $connection = $this->createMock(ConnectionInterface::class);
        $connection
            ->expects($this->at(0))
            ->method('addListener');

        $connection
            ->expects($this->at(1))
            ->method('receive')
            ->willReturn('Welcome');

        $connection
            ->expects($this->at(2))
            ->method('send')
            ->with("EHLO hostname\r\n");

        $connection
            ->expects($this->at(3))
            ->method('receive')
            ->willReturn("250-hello\r\n");

        $connection
            ->expects($this->at(4))
            ->method('receive')
            ->willReturn("250 STARTTLS\r\n");

        $connection
            ->expects($this->at(5))
            ->method('send')
            ->with("STARTTLS\r\n");

        $connection
            ->expects($this->at(6))
            ->method('receive')
            ->willReturn("220 OK\r\n");

        $connection
            ->expects($this->at(7))
            ->method('upgrade')
            ->with(STREAM_CRYPTO_METHOD_TLS_CLIENT);

        $connection
            ->expects($this->at(8))
            ->method('getMetaData')
            ->with(['crypto'])
            ->willReturn(['crypto' => ['something']]);

        $negotiator = new ConnectionNegotiation($connection, 'hostname', false);
        $negotiator->negotiate(new Client($connection));
    }

}