<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Protocol\Smtp\Request;

use Genkgo\Mail\EmailAddress;
use Genkgo\Mail\Protocol\ConnectionInterface;
use Genkgo\Mail\Protocol\Smtp\Request\RcptToCommand;
use Genkgo\TestMail\AbstractTestCase;

final class RcptToCommandTest extends AbstractTestCase
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
            ->with("RCPT TO:<me@localhost>");

        $command = new RcptToCommand(new EmailAddress('me@localhost'));
        $command->execute($connection);
    }
}
