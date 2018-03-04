<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Protocol\Smtp\Capability;

use Genkgo\Mail\Protocol\ConnectionInterface;
use Genkgo\Mail\Protocol\Smtp\Backend\ArrayBackend;
use Genkgo\Mail\Protocol\Smtp\Capability\RcptToCapability;
use Genkgo\Mail\Protocol\Smtp\Session;
use Genkgo\TestMail\AbstractTestCase;

final class RcptToTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_advertises_itself()
    {
        $capability = new RcptToCapability(new ArrayBackend([], new \ArrayObject()));

        $this->assertSame('RCPT TO', $capability->advertise());
    }

    /**
     * @test
     */
    public function it_accepts_addresses()
    {
        $connection = $this->createMock(ConnectionInterface::class);

        $connection
            ->expects($this->at(0))
            ->method('send')
            ->with('250 OK');

        $capability = new RcptToCapability(new ArrayBackend(['test@genkgo.nl'], new \ArrayObject()));

        $session = $capability->manifest($connection, (new Session())->withCommand('RCPT TO <test@genkgo.nl>'));
        $this->assertSame(Session::STATE_MESSAGE, $session->getState());
    }

    /**
     * @test
     */
    public function it_rejects_invalid_addresses()
    {
        $connection = $this->createMock(ConnectionInterface::class);

        $connection
            ->expects($this->at(0))
            ->method('send')
            ->with('501 Invalid recipient');

        $capability = new RcptToCapability(new ArrayBackend(['test@genkgo.nl'], new \ArrayObject()));

        $session = $capability->manifest($connection, (new Session())->withCommand('RCPT TO <test@@genkgo.nl>'));
        $this->assertSame(Session::STATE_CONNECTED, $session->getState());
    }

    /**
     * @test
     */
    public function it_rejects_unknown_mailbox()
    {
        $connection = $this->createMock(ConnectionInterface::class);

        $connection
            ->expects($this->at(0))
            ->method('send')
            ->with('550 Unknown mailbox');

        $capability = new RcptToCapability(new ArrayBackend(['test@genkgo.nl'], new \ArrayObject()));

        $session = $capability->manifest($connection, (new Session())->withCommand('RCPT TO <other@genkgo.nl>'));
        $this->assertSame(Session::STATE_CONNECTED, $session->getState());
    }
}
