<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Protocol\Smtp\Capability;

use Genkgo\Mail\Protocol\ConnectionInterface;
use Genkgo\Mail\Protocol\Smtp\Authentication\ArrayAuthentication;
use Genkgo\Mail\Protocol\Smtp\Capability\AuthLoginCapability;
use Genkgo\Mail\Protocol\Smtp\Session;
use Genkgo\TestMail\AbstractTestCase;

final class AuthLoginTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_advertises()
    {
        $capability = new AuthLoginCapability(
            new ArrayAuthentication(['test' => 'test'])
        );

        $this->assertSame('AUTH LOGIN', $capability->advertise());
    }

    /**
     * @test
     */
    public function it_can_login()
    {
        $connection = $this->createMock(ConnectionInterface::class);

        $connection
            ->expects($this->at(0))
            ->method('send');

        $connection
            ->expects($this->at(1))
            ->method('receive')
            ->willReturn(\base64_encode('test'));

        $connection
            ->expects($this->at(2))
            ->method('send');

        $connection
            ->expects($this->at(3))
            ->method('receive')
            ->willReturn(\base64_encode('test'));

        $connection
            ->expects($this->at(4))
            ->method('send')
            ->with('235 Authentication succeeded');

        $capability = new AuthLoginCapability(
            new ArrayAuthentication(['test' => 'test'])
        );

        $session = $capability->manifest($connection, new Session());
        $this->assertSame(Session::STATE_AUTHENTICATED, $session->getState());
    }

    /**
     * @test
     */
    public function it_refuses_to_login()
    {
        $connection = $this->createMock(ConnectionInterface::class);

        $connection
            ->expects($this->at(0))
            ->method('send');

        $connection
            ->expects($this->at(1))
            ->method('receive')
            ->willReturn(\base64_encode('test'));

        $connection
            ->expects($this->at(2))
            ->method('send');

        $connection
            ->expects($this->at(3))
            ->method('receive')
            ->willReturn(\base64_encode('test'));

        $connection
            ->expects($this->at(4))
            ->method('send')
            ->with('535 Authentication failed');

        $capability = new AuthLoginCapability(
            new ArrayAuthentication(['x' => 'y'])
        );

        $session = $capability->manifest($connection, new Session());
        $this->assertSame(Session::STATE_CONNECTED, $session->getState());
    }
}
