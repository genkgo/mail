<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Protocol\Smtp;

use Genkgo\Mail\Exception\ConnectionListenerException;
use Genkgo\Mail\Protocol\ConnectionInterface;
use Genkgo\Mail\Protocol\ConnectionListenerInterface;
use Genkgo\Mail\Protocol\Smtp\Server;
use Genkgo\TestMail\AbstractTestCase;

final class ServerTest extends AbstractTestCase
{

    /**
     * @test
     */
    public function it_listens()
    {
        $this->expectException(ConnectionListenerException::class);

        $connection = $this->createMock(ConnectionInterface::class);
        $connection
            ->expects($this->at(0))
            ->method('send')
            ->with("220 Welcome to Genkgo Mail Server\r\n");

        $connection
            ->expects($this->at(1))
            ->method('receive')
            ->willReturn("EHLO localhost\r\n");

        $connection
            ->expects($this->at(2))
            ->method('send')
            ->with("250 localhost Hello localhost\r\n");

        $connection
            ->expects($this->at(3))
            ->method('receive')
            ->willReturn("QUIT\r\n");

        $listener = $this->createMock(ConnectionListenerInterface::class);
        $listener
            ->expects($this->at(0))
            ->method('listen')
            ->willReturn($connection);

        $listener
            ->expects($this->at(1))
            ->method('listen')
            ->willThrowException(new ConnectionListenerException());

        $server = new Server($listener, [], 'localhost');
        $server->start();
    }

    /**
     * @test
     */
    public function it_does_not_accept_unknown_commands()
    {
        $this->expectException(ConnectionListenerException::class);

        $connection = $this->createMock(ConnectionInterface::class);
        $connection
            ->expects($this->at(0))
            ->method('send')
            ->with("220 Welcome to Genkgo Mail Server\r\n");

        $connection
            ->expects($this->at(1))
            ->method('receive')
            ->willReturn("UNKNOWN\r\n");

        $connection
            ->expects($this->at(2))
            ->method('send')
            ->with("500 unrecognized command\r\n");

        $connection
            ->expects($this->at(3))
            ->method('receive')
            ->willReturn("QUIT\r\n");

        $listener = $this->createMock(ConnectionListenerInterface::class);
        $listener
            ->expects($this->at(0))
            ->method('listen')
            ->willReturn($connection);

        $listener
            ->expects($this->at(1))
            ->method('listen')
            ->willThrowException(new ConnectionListenerException());

        $server = new Server($listener, [], 'localhost');
        $server->start();
    }
}