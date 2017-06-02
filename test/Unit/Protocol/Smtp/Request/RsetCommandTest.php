<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Protocol\Smtp\Request;

use Genkgo\Mail\Protocol\ConnectionInterface;
use Genkgo\Mail\Protocol\Smtp\Request\RsetCommand;
use Genkgo\TestMail\AbstractTestCase;

final class RsetCommandTest extends AbstractTestCase
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
            ->with("RSET\r\n");

        $command = new RsetCommand();
        $command->execute($connection);
    }

}