<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Protocol\Smtp\Request;

use Genkgo\Mail\Protocol\ConnectionInterface;
use Genkgo\Mail\Protocol\Smtp\Request\AuthLoginCommand;
use Genkgo\TestMail\AbstractTestCase;

final class AuthLoginCommandTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_executes()
    {
        $connection = $this->createMock(ConnectionInterface::class);
        $connection
            ->expects($this->once())
            ->method('send')
            ->with("AUTH LOGIN");

        $command = new AuthLoginCommand();
        $command->execute($connection);
    }
}
