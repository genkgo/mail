<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Protocol\Smtp;

use Genkgo\Mail\Protocol\Smtp\NullConnection;
use PHPUnit\Framework\TestCase;

class NullConnectionTest extends TestCase
{
    /**
     * @test
     */
    public function it_does_nothing_when_listener_added(): void
    {
        $connection = new NullConnection();
        $connection->addListener('test', function () {
        });

        // Test that no exception is thrown
        $this->addToAssertionCount(1);
    }

    /**
     * @test
     */
    public function it_does_nothing_when_connect_called(): void
    {
        $connection = new NullConnection();
        $connection->connect();

        // Test that no exception is thrown
        $this->addToAssertionCount(1);
    }

    /**
     * @test
     */
    public function it_does_nothing_when_disconnect_called(): void
    {
        $connection = new NullConnection();
        $connection->disconnect();

        // Test that no exception is thrown
        $this->addToAssertionCount(1);
    }

    public function providerSendReceive(): iterable
    {
        yield ['AUTH', '250 Ok'];
        yield ['HELO', '250 Ok'];
        yield ['EHLO', '250 Ok'];
        yield ['MAIL', '250 Ok'];
        yield ['RCPT', '250 Ok'];
        yield ['RSET', '250 Ok'];
        yield ['NOOP', '250 Ok'];
        yield ['DATA', '354 End data with <CR><LF>.<CR><LF>'];
        yield ['QUIT', '221 Bye'];
        yield ['VRFY', '252 null@null'];
        yield ['TEST', ''];
    }

    /**
     * @dataProvider providerSendReceive
     * @test
     */
    public function it_responds_correctly_to_send_commands(string $send, string $expect): void
    {
        $connection = new NullConnection();

        $connection->send($send);
        $receive = $connection->receive();

        self::assertSame($expect, $receive);
    }

    /**
     * @test
     */
    public function it_does_nothing_when_upgrade_called(): void
    {
        $connection = new NullConnection();
        $connection->upgrade(STREAM_CRYPTO_METHOD_TLS_CLIENT);

        // Test that no exception is thrown
        $this->addToAssertionCount(1);
    }

    /**
     * @test
     */
    public function it_does_nothing_when_timeout_called(): void
    {
        $connection = new NullConnection();
        $connection->timeout(3.1415);

        // Test that no exception is thrown
        $this->addToAssertionCount(1);
    }

    /**
     * @test
     */
    public function it_returns_empty_meta_data_array(): void
    {
        $connection = new NullConnection();

        self::assertSame([], $connection->getMetaData());
    }
}
