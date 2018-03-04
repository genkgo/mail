<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Protocol;

use Genkgo\Mail\Protocol\AppendCrlfConnection;
use Genkgo\Mail\Protocol\ConnectionInterface;
use Genkgo\TestMail\AbstractTestCase;

final class AppendCrlfConnectionTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_adds_a_crlf_when_sending()
    {
        $decorated = $this->createMock(ConnectionInterface::class);

        $decorated
            ->expects($this->once())
            ->method('send')
            ->with("line\r\n");

        $connection = new AppendCrlfConnection($decorated);
        $connection->send('line');
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

        $connection = new AppendCrlfConnection($decorated);
        $connection->addListener('xyz', function () {
        });
        $connection->connect();
        $connection->disconnect();
        $connection->receive();
        $connection->upgrade(STREAM_CRYPTO_METHOD_TLS_CLIENT);
        $connection->timeout(2);
        $connection->getMetaData(['xyz']);
    }
}
