<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Protocol\Smtp\Capability;

use Genkgo\Mail\Protocol\ConnectionInterface;
use Genkgo\Mail\Protocol\Smtp\Capability\ResetCapability;
use Genkgo\Mail\Protocol\Smtp\Session;
use Genkgo\TestMail\AbstractTestCase;

final class ResetTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_advertises_itself()
    {
        $capability = new ResetCapability();

        $this->assertSame('RSET', $capability->advertise());
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
            ->with('250 Reset OK');

        $capability = new ResetCapability();

        $session = $capability->manifest($connection, new Session());
        $this->assertSame(Session::STATE_CONNECTED, $session->getState());
    }
}
