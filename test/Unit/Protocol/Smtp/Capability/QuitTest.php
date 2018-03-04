<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Protocol\Smtp\Capability;

use Genkgo\Mail\Protocol\ConnectionInterface;
use Genkgo\Mail\Protocol\Smtp\Capability\QuitCapability;
use Genkgo\Mail\Protocol\Smtp\Session;
use Genkgo\TestMail\AbstractTestCase;

final class QuitTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_advertises_itself()
    {
        $capability = new QuitCapability();

        $this->assertSame('QUIT', $capability->advertise());
    }

    /**
     * @test
     */
    public function it_advertises_others()
    {
        $connection = $this->createMock(ConnectionInterface::class);

        $connection
            ->expects($this->at(0))
            ->method('send')
            ->with('221 Thank you for listening');

        $connection
            ->expects($this->at(1))
            ->method('disconnect');

        $capability = new QuitCapability();

        $session = $capability->manifest($connection, new Session());
        $this->assertSame(Session::STATE_DISCONNECT, $session->getState());
    }
}
