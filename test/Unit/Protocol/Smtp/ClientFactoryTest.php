<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Protocol\Smtp;

use Genkgo\Mail\Exception\ConnectionRefusedException;
use Genkgo\Mail\Protocol\ConnectionInterface;
use Genkgo\Mail\Protocol\Smtp\Client;
use Genkgo\Mail\Protocol\Smtp\ClientFactory;
use Genkgo\Mail\Protocol\Smtp\Request\NoopCommand;
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

        $this->assertNotSame($factory, $factory->withAuthentication(Client::AUTH_AUTO, 'x', 'y'));
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
    public function it_creates_connection_negotiator()
    {
        $at = -1;
        $callbacks = [];

        $connection = $this->createMock(ConnectionInterface::class);
        $connection
            ->expects($this->at(++$at))
            ->method('addListener')
            ->with(
                'connect',
                $this->callback(
                    function(\Closure $callback) use (&$callbacks) {
                        $callbacks[] = $callback;
                        return true;
                    }
                )
            );

        $connection
            ->expects($this->at(++$at))
            ->method('receive')
            ->willReturn('welcome');

        $connection
            ->expects($this->at(++$at))
            ->method('send')
            ->with("EHLO hostname\r\n");

        $connection
            ->expects($this->at(++$at))
            ->method('receive')
            ->willReturn("250 hello\r\n");

        $factory = (new ClientFactory($connection))
            ->withAllowInsecure()
            ->withEhlo('hostname');

        $factory->newClient();

        $this->assertCount(1, $callbacks);

        foreach ($callbacks as $callback) {
            $callback();
        }
    }

    /**
     * @test
     */
    public function it_creates_connection_and_authentication_negotiator()
    {
        $callbacks = [];
        $at = -1;

        $connection = $this->createMock(ConnectionInterface::class);

        $connection
            ->expects($this->at(++$at))
            ->method('addListener')
            ->with(
                'connect',
                $this->callback(
                    function(\Closure $callback) use (&$callbacks) {
                        $callbacks[] = $callback;
                        return true;
                    }
                )
            );

        $connection
            ->expects($this->at(++$at))
            ->method('receive')
            ->willReturn('welcome');

        $connection
            ->expects($this->at(++$at))
            ->method('send')
            ->with("EHLO 127.0.0.1\r\n");

        $connection
            ->expects($this->at(++$at))
            ->method('receive')
            ->willReturn("250 hello\r\n");

        $connection
            ->expects($this->at(++$at))
            ->method('send')
            ->with("EHLO 127.0.0.1\r\n");

        $connection
            ->expects($this->at(++$at))
            ->method('receive')
            ->willReturn("250-hello\r\n");

        $connection
            ->expects($this->at(++$at))
            ->method('receive')
            ->willReturn("250 AUTH LOGIN\r\n");

        $connection
            ->expects($this->at(++$at))
            ->method('send')
            ->with("AUTH LOGIN\r\n");

        $connection
            ->expects($this->at(++$at))
            ->method('receive')
            ->willReturn("334 OK\r\n");

        $connection
            ->expects($this->at(++$at))
            ->method('send')
            ->with("dXNlcg==\r\n");

        $connection
            ->expects($this->at(++$at))
            ->method('receive')
            ->willReturn("334 OK\r\n");

        $connection
            ->expects($this->at(++$at))
            ->method('send')
            ->with("cGFzcw==\r\n");

        $connection
            ->expects($this->at(++$at))
            ->method('receive')
            ->willReturn("200 OK\r\n");

        $factory = (new ClientFactory($connection))
            ->withAllowInsecure()
            ->withAuthentication(Client::AUTH_AUTO, 'user', 'pass');

        $factory->newClient();

        $this->assertCount(1, $callbacks);

        foreach ($callbacks as $callback) {
            $callback();
        }
    }

    /**
     * @test
     */
    public function it_constructs_tcp_from_data_source_name()
    {
        $this->expectException(ConnectionRefusedException::class);
        $this->expectExceptionMessage('Could not create plain tcp connection. Connection refused.');

        $factory = ClientFactory::fromString('smtp://user:pass@localhost/?ehlo=localhost&timeout=1');

        $factory
            ->newClient()
            ->request(new NoopCommand());
    }

    /**
     * @test
     */
    public function it_constructs_plain_tcp_from_data_source_name()
    {
        $this->expectException(ConnectionRefusedException::class);
        $this->expectExceptionMessage('Could not create plain tcp connection. Connection refused.');

        $factory = ClientFactory::fromString('smtp+plain://localhost/');

        $factory->newClient()->request(new NoopCommand());
    }

    /**
     * @test
     */
    public function it_constructs_tls_from_data_source_name()
    {
        $this->expectException(ConnectionRefusedException::class);
        $this->expectExceptionMessage('Could not create tls connection. Connection refused.');

        $factory = ClientFactory::fromString('smtp+tls://localhost/');

        $factory->newClient()->request(new NoopCommand());
    }

    /**
     * @test
     */
    public function it_constructs_ssl_from_data_source_name()
    {
        $this->expectException(ConnectionRefusedException::class);
        $this->expectExceptionMessage('Could not create ssl connection. Connection refused.');

        $factory = ClientFactory::fromString('smtp+ssl://localhost/');

        $factory->newClient()->request(new NoopCommand());
    }

    /**
     * @test
     */
    public function it_throws_when_incorrect_dsn()
    {
        $this->expectException(\InvalidArgumentException::class);

        ClientFactory::fromString('something');
    }

    /**
     * @test
     */
    public function it_throws_when_incorrect_protocol()
    {
        $this->expectException(\InvalidArgumentException::class);

        ClientFactory::fromString('xyz://host');
    }

}