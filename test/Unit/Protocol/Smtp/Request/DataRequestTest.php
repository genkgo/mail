<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Protocol\Smtp\Request;

use Genkgo\Mail\Protocol\ConnectionInterface;
use Genkgo\Mail\Protocol\Smtp\Request\DataRequest;
use Genkgo\Mail\Stream\StringStream;
use Genkgo\TestMail\AbstractTestCase;

final class DataRequestTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_executes()
    {
        $connection = $this->createMock(ConnectionInterface::class);
        $connection
            ->expects($this->at(0))
            ->method('send')
            ->with("test");

        $connection
            ->expects($this->at(1))
            ->method('send')
            ->with(".");

        $command = new DataRequest(new StringStream('test'));
        $command->execute($connection);
    }

    /**
     * @test
     */
    public function it_escapes_lines_starting_with_a_dot()
    {
        $connection = $this->createMock(ConnectionInterface::class);
        $connection
            ->expects($this->at(0))
            ->method('send')
            ->with("..test");

        $connection
            ->expects($this->at(1))
            ->method('send')
            ->with(".");

        $command = new DataRequest(new StringStream('.test'));
        $command->execute($connection);
    }

    /**
     * @test
     */
    public function it_trims_carriage_returns()
    {
        $connection = $this->createMock(ConnectionInterface::class);
        $connection
            ->expects($this->at(0))
            ->method('send')
            ->with("test");

        $connection
            ->expects($this->at(1))
            ->method('send')
            ->with(".");

        $command = new DataRequest(new StringStream("test\r"));
        $command->execute($connection);
    }

    /**
     * @test
     */
    public function it_sends_lines()
    {
        $connection = $this->createMock(ConnectionInterface::class);
        $connection
            ->expects($this->at(0))
            ->method('send')
            ->with("test");

        $connection
            ->expects($this->at(1))
            ->method('send')
            ->with("test");

        $connection
            ->expects($this->at(2))
            ->method('send')
            ->with("test");

        $connection
            ->expects($this->at(3))
            ->method('send')
            ->with("test");

        $connection
            ->expects($this->at(4))
            ->method('send')
            ->with("");

        $connection
            ->expects($this->at(5))
            ->method('send')
            ->with(".");

        $command = new DataRequest(new StringStream(\str_repeat("test\r\ntest\r\n", 2)));
        $command->execute($connection);
    }

    /**
     * @test
     */
    public function it_does_not_add_lines()
    {
        $connection = $this->createMock(ConnectionInterface::class);
        $connection
            ->expects($this->exactly(3))
            ->method('send');

        $command = new DataRequest(new StringStream(\str_repeat("a", 996) . "\r\naaaaa"));
        $command->execute($connection);
    }

    /**
     * @test
     */
    public function it_rewinds_stream_beforehand()
    {
        $connection = $this->createMock(ConnectionInterface::class);
        $connection
            ->expects($this->at(0))
            ->method('send')
            ->with("test");

        $connection
            ->expects($this->at(1))
            ->method('send')
            ->with(".");

        $connection
            ->expects($this->at(2))
            ->method('send')
            ->with("test");

        $connection
            ->expects($this->at(3))
            ->method('send')
            ->with(".");

        $stream = new StringStream('test');

        $command1 = new DataRequest($stream);
        $command1->execute($connection);

        $command2 = new DataRequest($stream);
        $command2->execute($connection);
    }
}
