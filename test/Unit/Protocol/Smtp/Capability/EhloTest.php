<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Protocol\Smtp\Capability;

use Genkgo\Mail\Protocol\ConnectionInterface;
use Genkgo\Mail\Protocol\Smtp\Authentication\ArrayAuthentication;
use Genkgo\Mail\Protocol\Smtp\Capability\AuthLoginCapability;
use Genkgo\Mail\Protocol\Smtp\Capability\EhloCapability;
use Genkgo\Mail\Protocol\Smtp\Session;
use Genkgo\TestMail\AbstractTestCase;

final class EhloTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_advertises_itself()
    {
        $capability = new EhloCapability('localhost', []);

        $this->assertSame('EHLO', $capability->advertise());
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
            ->with('250-localhost Hello localhost');

        $connection
            ->expects($this->at(1))
            ->method('send')
            ->with('250 AUTH LOGIN');

        $capability = new EhloCapability('localhost', [new AuthLoginCapability(new ArrayAuthentication([]))]);

        $session = $capability->manifest($connection, (new Session())->withCommand('EHLO localhost'));
        $this->assertSame(Session::STATE_EHLO, $session->getState());
    }
}
