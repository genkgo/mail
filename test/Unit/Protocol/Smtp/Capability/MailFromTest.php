<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Protocol\Smtp\Capability;

use Genkgo\Mail\Protocol\ConnectionInterface;
use Genkgo\Mail\Protocol\Smtp\Capability\MailFromCapability;
use Genkgo\Mail\Protocol\Smtp\Session;
use Genkgo\TestMail\AbstractTestCase;

final class MailFromTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_advertises_itself()
    {
        $capability = new MailFromCapability();

        $this->assertSame('MAIL FROM', $capability->advertise());
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

        $capability = new MailFromCapability();

        $session = $capability->manifest($connection, (new Session())->withCommand('MAIL FROM test@genkgo.nl'));
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
            ->with('501 Invalid envelope');

        $capability = new MailFromCapability();

        $session = $capability->manifest($connection, (new Session())->withCommand('MAIL FROM test@@genkgo.nl'));
        $this->assertSame(Session::STATE_CONNECTED, $session->getState());
    }
}
