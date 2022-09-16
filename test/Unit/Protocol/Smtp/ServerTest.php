<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Protocol\Smtp;

use Genkgo\Mail\Exception\ConnectionBrokenException;
use Genkgo\Mail\Exception\ConnectionClosedException;
use Genkgo\Mail\Exception\ConnectionListenerException;
use Genkgo\Mail\Exception\ConnectionTimeoutException;
use Genkgo\Mail\Protocol\ConnectionInterface;
use Genkgo\Mail\Protocol\ConnectionListenerInterface;
use Genkgo\Mail\Protocol\Smtp\Server;
use Genkgo\TestMail\AbstractTestCase;

final class ServerTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_listens(): void
    {
        $this->expectException(ConnectionListenerException::class);

        $connection = $this->createMock(ConnectionInterface::class);
        $connection
            ->expects($this->exactly(3))
            ->method('send')
            ->withConsecutive(
                ["220 Welcome to Genkgo Mail Server\r\n"],
                ["250 localhost Hello localhost\r\n"],
                ["221 Thank you for listening\r\n"]
            );

        $connection
            ->expects($this->exactly(2))
            ->method('receive')
            ->willReturnOnConsecutiveCalls(
                "EHLO localhost\r\n",
                "QUIT\r\n",
            );

        $listener = $this->createMock(ConnectionListenerInterface::class);
        $listener
            ->expects($this->exactly(2))
            ->method('listen')
            ->willReturnOnConsecutiveCalls(
                $connection,
                $this->throwException(new ConnectionListenerException())
            );

        $server = new Server($listener, [], 'localhost');
        $server->start();
    }

    /**
     * @test
     */
    public function it_does_not_accept_unknown_commands(): void
    {
        $this->expectException(ConnectionListenerException::class);

        $connection = $this->createMock(ConnectionInterface::class);
        $connection
            ->expects($this->exactly(3))
            ->method('send')
            ->withConsecutive(
                ["220 Welcome to Genkgo Mail Server\r\n"],
                ["500 unrecognized command\r\n"],
                ["221 Thank you for listening\r\n"]
            );

        $connection
            ->expects($this->exactly(2))
            ->method('receive')
            ->willReturnOnConsecutiveCalls(
                "UNKNOWN\r\n",
                "QUIT\r\n"
            );

        $listener = $this->createMock(ConnectionListenerInterface::class);
        $listener
            ->expects($this->exactly(2))
            ->method('listen')
            ->willReturnOnConsecutiveCalls(
                $connection,
                $this->throwException(new ConnectionListenerException())
            );

        $server = new Server($listener, [], 'localhost');
        $server->start();
    }

    /**
     * @test
     */
    public function it_closes_connection_after_timeout(): void
    {
        $this->expectException(ConnectionListenerException::class);

        $connection = $this->createMock(ConnectionInterface::class);
        $connection
            ->expects($this->exactly(2))
            ->method('send')
            ->withConsecutive(
                ["220 Welcome to Genkgo Mail Server\r\n"],
                ["421 command timeout - closing connection\r\n"]
            );

        $connection
            ->expects($this->exactly(1))
            ->method('receive')
            ->willThrowException(new ConnectionTimeoutException());

        $connection
            ->expects($this->exactly(1))
            ->method('disconnect');

        $listener = $this->createMock(ConnectionListenerInterface::class);
        $listener
            ->expects($this->exactly(2))
            ->method('listen')
            ->willReturnOnConsecutiveCalls(
                $connection,
                $this->throwException(new ConnectionListenerException())
            );

        $server = new Server($listener, [], 'localhost');
        $server->start();
    }

    /**
     * @test
     */
    public function it_closes_connection_when_client_closed_it(): void
    {
        $this->expectException(ConnectionListenerException::class);

        $connection = $this->createMock(ConnectionInterface::class);
        $connection
            ->expects($this->exactly(1))
            ->method('send')
            ->with("220 Welcome to Genkgo Mail Server\r\n");

        $connection
            ->expects($this->exactly(1))
            ->method('receive')
            ->willThrowException(new ConnectionClosedException());

        $connection
            ->expects($this->exactly(1))
            ->method('disconnect');

        $listener = $this->createMock(ConnectionListenerInterface::class);
        $listener
            ->expects($this->exactly(2))
            ->method('listen')
            ->willReturnOnConsecutiveCalls(
                $connection,
                $this->throwException(new ConnectionListenerException())
            );

        $server = new Server($listener, [], 'localhost');
        $server->start();
    }

    /**
     * @test
     */
    public function it_closes_broken_connections_and_notifies_client(): void
    {
        $this->expectException(ConnectionListenerException::class);

        $connection = $this->createMock(ConnectionInterface::class);
        $connection
            ->expects($this->exactly(2))
            ->method('send')
            ->withConsecutive(
                ["220 Welcome to Genkgo Mail Server\r\n"],
                ["554 transaction failed, unexpected value - closing connection\r\n"]
            );

        $connection
            ->expects($this->exactly(1))
            ->method('receive')
            ->willThrowException(new ConnectionBrokenException());

        $connection
            ->expects($this->exactly(1))
            ->method('disconnect');

        $listener = $this->createMock(ConnectionListenerInterface::class);
        $listener
            ->expects($this->exactly(2))
            ->method('listen')
            ->willReturnOnConsecutiveCalls(
                $connection,
                $this->throwException(new ConnectionListenerException())
            );

        $server = new Server($listener, [], 'localhost');
        $server->start();
    }

    /**
     * @test
     */
    public function it_closes_broken_connections_and_disconnects_if_notification_fails(): void
    {
        $this->expectException(ConnectionListenerException::class);

        $connection = $this->createMock(ConnectionInterface::class);
        $connection
            ->expects($this->exactly(2))
            ->method('send')
            ->withConsecutive(
                ["220 Welcome to Genkgo Mail Server\r\n"],
                ["554 transaction failed, unexpected value - closing connection\r\n"]
            )
            ->willReturnOnConsecutiveCalls(
                1,
                $this->throwException(new \UnexpectedValueException('Some unknown exception'))
            );

        $connection
            ->expects($this->exactly(1))
            ->method('receive')
            ->willThrowException(new ConnectionBrokenException());

        $connection
            ->expects($this->exactly(1))
            ->method('disconnect');

        $listener = $this->createMock(ConnectionListenerInterface::class);
        $listener
            ->expects($this->exactly(2))
            ->method('listen')
            ->willReturnOnConsecutiveCalls(
                $connection,
                $this->throwException(new ConnectionListenerException())
            );

        $server = new Server($listener, [], 'localhost');
        $server->start();
    }
}
