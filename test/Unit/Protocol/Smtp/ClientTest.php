<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Protocol\Smtp;

use Genkgo\Mail\Protocol\ConnectionInterface;
use Genkgo\Mail\Protocol\Smtp\Client;
use Genkgo\Mail\Protocol\Smtp\RequestInterface;
use Genkgo\TestMail\AbstractTestCase;

final class ClientTest extends AbstractTestCase
{

    /**
     * @test
     */
    public function it_creates_a_reply()
    {
        $connection = $this->createMock(ConnectionInterface::class);
        $command = $this->createMock(RequestInterface::class);

        $command
            ->expects($this->once())
            ->method('execute');

        $connection
            ->expects($this->once())
            ->method('receive')
            ->willReturn('220 OK');

        $client = new Client($connection);
        $reply = $client->request($command);

        $this->assertEquals(['OK'], $reply->getMessages());
    }

    /**
     * @test
     */
    public function it_creates_a_reply_with_multiple_lines()
    {
        $connection = $this->createMock(ConnectionInterface::class);
        $command = $this->createMock(RequestInterface::class);

        $command
            ->expects($this->once())
            ->method('execute');

        $connection
            ->expects($this->at(0))
            ->method('receive')
            ->willReturn('220 welcome');

        $connection
            ->expects($this->at(1))
            ->method('receive')
            ->willReturn('250-STARTTLS');

        $connection
            ->expects($this->at(2))
            ->method('receive')
            ->willReturn('250 HELP');

        $client = new Client($connection);
        $reply = $client->request($command);

        $this->assertEquals(['STARTTLS', 'HELP'], $reply->getMessages());
    }

}