<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Protocol\Smtp\Request;

use Genkgo\Mail\Protocol\ConnectionInterface;
use Genkgo\Mail\Protocol\Smtp\Request\DataRequest;
use Genkgo\Mail\Stream\BitEncodedStream;
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
            ->with("test\r\n");

        $connection
            ->expects($this->at(1))
            ->method('send')
            ->with(".\r\n");

        $command = new DataRequest(new BitEncodedStream('test'));
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
            ->with("..test\r\n");

        $connection
            ->expects($this->at(1))
            ->method('send')
            ->with(".\r\n");

        $command = new DataRequest(new BitEncodedStream('.test'));
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
            ->with("test\r\n");

        $connection
            ->expects($this->at(1))
            ->method('send')
            ->with(".\r\n");

        $command = new DataRequest(new BitEncodedStream("test\r"));
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
            ->with("test\r\n");

        $connection
            ->expects($this->at(1))
            ->method('send')
            ->with("test\r\n");

        $connection
            ->expects($this->at(2))
            ->method('send')
            ->with("test\r\n");

        $connection
            ->expects($this->at(3))
            ->method('send')
            ->with("test\r\n");

        $connection
            ->expects($this->at(4))
            ->method('send')
            ->with("\r\n");

        $connection
            ->expects($this->at(5))
            ->method('send')
            ->with(".\r\n");

        $command = new DataRequest(new BitEncodedStream(str_repeat("test\r\ntest\r\n", 2)));
        $command->execute($connection);
    }

}