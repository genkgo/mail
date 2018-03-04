<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Protocol\Imap;

use Genkgo\Mail\Exception\ConnectionRefusedException;
use Genkgo\Mail\Protocol\ConnectionInterface;
use Genkgo\Mail\Protocol\CryptoConstant;
use Genkgo\Mail\Protocol\Imap\Client;
use Genkgo\Mail\Protocol\Imap\ClientFactory;
use Genkgo\Mail\Protocol\Imap\Request\NoopCommand;
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
        $this->assertNotSame($factory, $factory->withTimeout(10));
        $this->assertNotSame($factory, $factory->withInsecureConnectionAllowed());
        $this->assertNotSame($factory, $factory->withStartTls(CryptoConstant::getDefaultMethod(PHP_VERSION)));
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
        $callback = function () {
        };
        $at = -1;

        $connection = $this->createMock(ConnectionInterface::class);
        $connection
            ->expects($this->at(++$at))
            ->method('addListener')
            ->with(
                'connect',
                $this->callback(
                    function (\Closure $closure) use (&$callback) {
                        $callback = $closure;
                        return true;
                    }
                )
            );

        $connection
            ->expects($this->at(++$at))
            ->method('connect');

        $connection
            ->expects($this->at(++$at))
            ->method('receive')
            ->willReturn('* CAPABILITY STARTTLS');

        $connection
            ->expects($this->at(++$at))
            ->method('getMetaData')
            ->willReturn([]);

        $connection
            ->expects($this->at(++$at))
            ->method('send')
            ->with("TAG1 CAPABILITY\r\n");

        $connection
            ->expects($this->at(++$at))
            ->method('receive')
            ->willReturn('* CAPABILITY IMAP4rev1 STARTTLS AUTH=PLAIN');

        $connection
            ->expects($this->at(++$at))
            ->method('receive')
            ->willReturn('TAG1 OK');

        $connection
            ->expects($this->at(++$at))
            ->method('send')
            ->with("TAG2 STARTTLS\r\n");

        $connection
            ->expects($this->at(++$at))
            ->method('receive')
            ->willReturn('TAG2 OK');

        $connection
            ->expects($this->at(++$at))
            ->method('upgrade');

        $connection
            ->expects($this->at(++$at))
            ->method('getMetaData')
            ->willReturn(['crypto' => []]);

        $factory = new ClientFactory($connection);
        $factory->newClient();

        $callback();
    }

    /**
     * @test
     */
    public function it_creates_connection_and_authentication_negotiator()
    {
        $callback = function () {
        };
        $at = -1;

        $connection = $this->createMock(ConnectionInterface::class);
        $connection
            ->expects($this->at(++$at))
            ->method('addListener')
            ->with(
                'connect',
                $this->callback(
                    function (\Closure $closure) use (&$callback) {
                        $callback = $closure;
                        return true;
                    }
                )
            );

        $connection
            ->expects($this->at(++$at))
            ->method('connect');

        $connection
            ->expects($this->at(++$at))
            ->method('receive')
            ->willReturn('* CAPABILITY STARTTLS');

        $connection
            ->expects($this->at(++$at))
            ->method('send')
            ->with("TAG1 CAPABILITY\r\n");

        $connection
            ->expects($this->at(++$at))
            ->method('receive')
            ->willReturn('* CAPABILITY IMAP4rev1 STARTTLS AUTH=PLAIN');

        $connection
            ->expects($this->at(++$at))
            ->method('receive')
            ->willReturn('TAG1 OK');

        $connection
            ->expects($this->at(++$at))
            ->method('send')
            ->with("TAG2 AUTHENTICATE PLAIN\r\n");

        $connection
            ->expects($this->at(++$at))
            ->method('receive')
            ->willReturn('+ Continue');

        $connection
            ->expects($this->at(++$at))
            ->method('send')
            ->with(\base64_encode("\0username\0password"). "\r\n");

        $connection
            ->expects($this->at(++$at))
            ->method('receive')
            ->willReturn('TAG2 OK');

        $factory = new ClientFactory($connection);
        $factory
            ->withAuthentication(Client::AUTH_AUTO, 'username', 'password')
            ->withoutStartTls()
            ->newClient();

        $callback();
    }

    /**
     * @test
     */
    public function it_uses_only_welcome()
    {
        $callback = function () {
        };
        $at = -1;

        $connection = $this->createMock(ConnectionInterface::class);
        $connection
            ->expects($this->at(++$at))
            ->method('addListener')
            ->with(
                'connect',
                $this->callback(
                    function (\Closure $closure) use (&$callback) {
                        $callback = $closure;
                        return true;
                    }
                )
            );

        $connection
            ->expects($this->at(++$at))
            ->method('connect');

        $connection
            ->expects($this->at(++$at))
            ->method('receive')
            ->willReturn('* CAPABILITY STARTTLS');

        $factory = new ClientFactory($connection);
        $factory
            ->withoutStartTls()
            ->newClient();

        $callback();
    }

    /**
     * @test
     */
    public function it_constructs_tcp_from_data_source_name()
    {
        $this->expectException(ConnectionRefusedException::class);

        $factory = ClientFactory::fromString(
            'imap://user:pass@localhost/?timeout=1&reconnectAfter=PT1S'
        );

        $client = $factory->newClient();
        $client->emit(new NoopCommand($client->newTag()));
    }

    /**
     * @test
     */
    public function it_constructs_plain_tcp_from_data_source_name()
    {
        $this->expectException(ConnectionRefusedException::class);

        $factory = ClientFactory::fromString('imap-starttls://localhost/');

        $client = $factory->newClient();
        $client->emit(new NoopCommand($client->newTag()));
    }

    /**
     * @test
     */
    public function it_constructs_tls_from_data_source_name()
    {
        $this->expectException(ConnectionRefusedException::class);

        $factory = ClientFactory::fromString('imaps://localhost/');

        $client = $factory->newClient();
        $client->emit(new NoopCommand($client->newTag()));
    }

    /**
     * @test
     */
    public function it_constructs_specific_tls_version_from_data_source_name()
    {
        $this->expectException(ConnectionRefusedException::class);

        $version = STREAM_CRYPTO_METHOD_TLSv1_1_CLIENT;

        $factory = ClientFactory::fromString('imaps://localhost/?crypto=' . $version);

        $client = $factory->newClient();
        $client->emit(new NoopCommand($client->newTag()));
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
