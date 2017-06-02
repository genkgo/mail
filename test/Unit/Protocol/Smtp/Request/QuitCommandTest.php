<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Protocol\Smtp\Request;

use Genkgo\Mail\Protocol\ConnectionInterface;
use Genkgo\Mail\Protocol\Smtp\Request\QuitCommand;
use Genkgo\TestMail\AbstractTestCase;

final class QuitCommandTest extends AbstractTestCase
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
            ->with("QUIT\r\n");

        $connection
            ->expects($this->at(1))
            ->method('disconnect');

        $command = new QuitCommand();
        $command->execute($connection);
    }

}