<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Protocol;

use Genkgo\Mail\Protocol\ConnectionInterface;
use Genkgo\Mail\Protocol\ReconnectAfterConnection;
use Genkgo\TestMail\AbstractTestCase;

final class ReconnectAfterConnectionTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_reconnects_when_time_passed()
    {
        $decorated = $this->createMock(ConnectionInterface::class);

        $decorated
            ->expects($this->at(0))
            ->method('connect');

        $decorated
            ->expects($this->at(1))
            ->method('disconnect');

        $decorated
            ->expects($this->at(2))
            ->method('connect');

        $decorated
            ->expects($this->at(3))
            ->method('send')
            ->with('xyz');

        $connection = new ReconnectAfterConnection($decorated, new \DateInterval('PT0S'));
        $connection->connect();
        $connection->send('xyz');
    }

    /**
     * @test
     */
    public function it_decorates_another_connection()
    {
        $decorated = $this->createMock(ConnectionInterface::class);

        $decorated
            ->expects($this->once())
            ->method('addListener');

        $decorated
            ->expects($this->once())
            ->method('connect');

        $decorated
            ->expects($this->once())
            ->method('disconnect');

        $decorated
            ->expects($this->once())
            ->method('receive');

        $decorated
            ->expects($this->once())
            ->method('upgrade')
            ->with(STREAM_CRYPTO_METHOD_TLS_CLIENT);

        $decorated
            ->expects($this->once())
            ->method('timeout')
            ->with(2);

        $decorated
            ->expects($this->once())
            ->method('getMetaData')
            ->with(['xyz']);

        $connection = new ReconnectAfterConnection($decorated, new \DateInterval('P1M'));
        $connection->addListener('xyz', function  () {});
        $connection->connect();
        $connection->upgrade(STREAM_CRYPTO_METHOD_TLS_CLIENT);
        $connection->timeout(2);
        $connection->getMetaData(['xyz']);
        $connection->send('xyz');
        $connection->receive();
        $connection->disconnect();
    }

    /**
     * @test
     */
    public function it_throws_when_something_is_send_while_there_is_no_connection()
    {
        $this->expectException(\RuntimeException::class);

        $decorated = $this->createMock(ConnectionInterface::class);

        $connection = new ReconnectAfterConnection($decorated, new \DateInterval('P1M'));
        $connection->send('line');
    }
}