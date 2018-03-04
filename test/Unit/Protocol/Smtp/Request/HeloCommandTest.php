<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Protocol\Smtp\Request;

use Genkgo\Mail\Protocol\ConnectionInterface;
use Genkgo\Mail\Protocol\Smtp\Request\HeloCommand;
use Genkgo\TestMail\AbstractTestCase;

final class HeloCommandTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_executes(): void
    {
        $connection = $this->createMock(ConnectionInterface::class);

        $connection
            ->expects($this->at(0))
            ->method('send')
            ->with('HELO host.example.com');

        $command = new HeloCommand('host.example.com');
        $command->execute($connection);
    }
}
