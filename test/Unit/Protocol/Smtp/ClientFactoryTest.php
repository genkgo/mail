<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Protocol\Smtp;

use Genkgo\Mail\Protocol\ConnectionInterface;
use Genkgo\Mail\Protocol\Smtp\ClientFactory;
use Genkgo\TestMail\AbstractTestCase;

final class ClientFactoryTest extends AbstractTestCase
{

    /**
     * @test
     */
    public function it_is_immutable()
    {
        $connection = $this->createMock(ConnectionInterface::class);
        $factory = new ClientFactory($connection);

        $this->assertNotSame($factory, $factory->withAuthentication(ClientFactory::AUTH_AUTO, 'x', 'y'));
        $this->assertNotSame($factory, $factory->withEhlo('127.0.0.1'));
        $this->assertNotSame($factory, $factory->withTimeout(10));
    }

    /**
     * @test
     */
    public function it_throws_when_using_wrong_auth_method()
    {
        $this->expectException(\InvalidArgumentException::class);
        $connection = $this->createMock(ConnectionInterface::class);

        $factory = new ClientFactory($connection);
        $factory->withAuthentication(99, 'x', 'y');
    }

    /**
     * @test
     */
    public function it_is_sends_ehlo()
    {
        $connection = $this->createMock(ConnectionInterface::class);
        $connection
            ->expects($this->at(0))
            ->method('connect');

        $connection
            ->expects($this->at(1))
            ->method('send')
            ->with("EHLO hostname\r\n");

        $connection
            ->expects($this->at(2))
            ->method('receive')
            ->willReturn("250 hello\r\n");

        $factory = new ClientFactory($connection);
        $factory = $factory->withEhlo('hostname');

        $factory->newClient();
    }

    /**
     * @test
     */
    public function it_is_sends_auth_plain()
    {
        $connection = $this->createMock(ConnectionInterface::class);
        $connection
            ->expects($this->at(0))
            ->method('connect');

        $connection
            ->expects($this->at(1))
            ->method('send')
            ->with("EHLO 127.0.0.1\r\n");

        $connection
            ->expects($this->at(2))
            ->method('receive')
            ->willReturn("250 hello\r\n");

        $connection
            ->expects($this->at(3))
            ->method('send')
            ->with("AUTH PLAIN\r\n");

        $connection
            ->expects($this->at(4))
            ->method('receive')
            ->willReturn("334 OK\r\n");

        $connection
            ->expects($this->at(5))
            ->method('send')
            ->with("AHBhc3MAdXNlcg==\r\n");

        $connection
            ->expects($this->at(6))
            ->method('receive')
            ->willReturn("200 OK\r\n");

        $factory = new ClientFactory($connection);
        $factory = $factory->withAuthentication(ClientFactory::AUTH_PLAIN, 'user', 'pass');

        $factory->newClient();
    }

    /**
     * @test
     */
    public function it_is_sends_auth_login()
    {
        $connection = $this->createMock(ConnectionInterface::class);
        $connection
            ->expects($this->at(0))
            ->method('connect');

        $connection
            ->expects($this->at(1))
            ->method('send')
            ->with("EHLO 127.0.0.1\r\n");

        $connection
            ->expects($this->at(2))
            ->method('receive')
            ->willReturn("250 hello\r\n");

        $connection
            ->expects($this->at(3))
            ->method('send')
            ->with("AUTH LOGIN\r\n");

        $connection
            ->expects($this->at(4))
            ->method('receive')
            ->willReturn("334 OK\r\n");

        $connection
            ->expects($this->at(5))
            ->method('send')
            ->with("cGFzcw==\r\n");

        $connection
            ->expects($this->at(6))
            ->method('receive')
            ->willReturn("334 OK\r\n");

        $connection
            ->expects($this->at(7))
            ->method('send')
            ->with("dXNlcg==\r\n");

        $connection
            ->expects($this->at(8))
            ->method('receive')
            ->willReturn("200 OK\r\n");

        $factory = new ClientFactory($connection);
        $factory = $factory->withAuthentication(ClientFactory::AUTH_LOGIN, 'user', 'pass');

        $factory->newClient();
    }

    /**
     * @test
     */
    public function it_is_sends_auth_auto()
    {
        $connection = $this->createMock(ConnectionInterface::class);
        $connection
            ->expects($this->at(0))
            ->method('connect');

        $connection
            ->expects($this->at(1))
            ->method('send')
            ->with("EHLO 127.0.0.1\r\n");

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
            ->willReturn("334 OK\r\n");

        $connection
            ->expects($this->at(6))
            ->method('send')
            ->with("cGFzcw==\r\n");

        $connection
            ->expects($this->at(7))
            ->method('receive')
            ->willReturn("334 OK\r\n");

        $connection
            ->expects($this->at(8))
            ->method('send')
            ->with("dXNlcg==\r\n");

        $connection
            ->expects($this->at(9))
            ->method('receive')
            ->willReturn("200 OK\r\n");

        $factory = new ClientFactory($connection);
        $factory = $factory->withAuthentication(ClientFactory::AUTH_AUTO, 'user', 'pass');

        $factory->newClient();
    }

    /**
     * @test
     */
    public function it_throw_when_auto_and_not_advertised()
    {
        $this->expectException(\RuntimeException::class);

        $connection = $this->createMock(ConnectionInterface::class);
        $connection
            ->expects($this->at(0))
            ->method('connect');

        $connection
            ->expects($this->at(1))
            ->method('send')
            ->with("EHLO 127.0.0.1\r\n");

        $connection
            ->expects($this->at(2))
            ->method('receive')
            ->willReturn("250 hello\r\n");

        $factory = new ClientFactory($connection);
        $factory = $factory->withAuthentication(ClientFactory::AUTH_AUTO, 'user', 'pass');

        $factory->newClient();
    }

    /**
     * @test
     */
    public function it_is_sends_starttls_when_advertised()
    {
        $connection = $this->createMock(ConnectionInterface::class);
        $connection
            ->expects($this->at(0))
            ->method('connect');

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
            ->willReturn("250 STARTTLS\r\n");

        $connection
            ->expects($this->at(4))
            ->method('send')
            ->with("STARTTLS\r\n");

        $connection
            ->expects($this->at(5))
            ->method('receive')
            ->willReturn("220 OK\r\n");

        $connection
            ->expects($this->at(6))
            ->method('upgrade')
            ->with(STREAM_CRYPTO_METHOD_TLS_CLIENT);

        $factory = new ClientFactory($connection);
        $factory = $factory->withEhlo('hostname');

        $factory->newClient();
    }

}