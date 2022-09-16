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
    public function it_executes(): void
    {
        $connection = $this->createMock(ConnectionInterface::class);
        $connection
            ->expects($this->exactly(2))
            ->method('send')
            ->withConsecutive(
                ["test"],
                ['.']
            );

        $command = new DataRequest(new StringStream('test'));
        $command->execute($connection);
    }

    /**
     * @test
     */
    public function it_escapes_lines_starting_with_a_dot(): void
    {
        $connection = $this->createMock(ConnectionInterface::class);
        $connection
            ->expects($this->exactly(2))
            ->method('send')
            ->withConsecutive(
                ["..test"],
                ['.']
            );

        $command = new DataRequest(new StringStream('.test'));
        $command->execute($connection);
    }

    /**
     * @test
     */
    public function it_trims_carriage_returns(): void
    {
        $connection = $this->createMock(ConnectionInterface::class);
        $connection
            ->expects($this->exactly(2))
            ->method('send')
            ->withConsecutive(
                ["test"],
                ['.']
            );

        $command = new DataRequest(new StringStream("test\r"));
        $command->execute($connection);
    }

    /**
     * @test
     */
    public function it_sends_lines(): void
    {
        $connection = $this->createMock(ConnectionInterface::class);
        $connection
            ->expects($this->exactly(6))
            ->method('send')
            ->withConsecutive(
                ["test"],
                ["test"],
                ["test"],
                ["test"],
                [""],
                ['.']
            );

        $command = new DataRequest(new StringStream(\str_repeat("test\r\ntest\r\n", 2)));
        $command->execute($connection);
    }

    /**
     * @test
     */
    public function it_does_not_add_lines(): void
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
    public function it_rewinds_stream_beforehand(): void
    {
        $connection = $this->createMock(ConnectionInterface::class);
        $connection
            ->expects($this->exactly(4))
            ->method('send')
            ->withConsecutive(
                ["test"],
                ['.'],
                ['test'],
                ['.']
            );

        $stream = new StringStream('test');

        $command1 = new DataRequest($stream);
        $command1->execute($connection);

        $command2 = new DataRequest($stream);
        $command2->execute($connection);
    }
}
