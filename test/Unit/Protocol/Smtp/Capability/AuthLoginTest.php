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
    public function it_advertises(): void
    {
        $capability = new AuthLoginCapability(
            new ArrayAuthentication(['test' => 'test'])
        );

        $this->assertSame('AUTH LOGIN', $capability->advertise());
    }

    /**
     * @test
     */
    public function it_can_login(): void
    {
        $connection = $this->createMock(ConnectionInterface::class);

        $connection
            ->expects($this->exactly(3))
            ->method('send')
            ->withConsecutive(
                ['330 Please send me your username'],
                ['330 Please send me your password'],
                ['235 Authentication succeeded']
            );

        $connection
            ->expects($this->exactly(2))
            ->method('receive')
            ->willReturn(\base64_encode('test'), );

        $capability = new AuthLoginCapability(
            new ArrayAuthentication(['test' => 'test'])
        );

        $session = $capability->manifest($connection, new Session());
        $this->assertSame(Session::STATE_AUTHENTICATED, $session->getState());
    }

    /**
     * @test
     */
    public function it_refuses_to_login(): void
    {
        $connection = $this->createMock(ConnectionInterface::class);

        $connection
            ->expects($this->exactly(3))
            ->method('send')
            ->withConsecutive(
                ['330 Please send me your username'],
                ['330 Please send me your password'],
                ['535 Authentication failed']
            );

        $connection
            ->expects($this->exactly(2))
            ->method('receive')
            ->willReturn(\base64_encode('test'));

        $capability = new AuthLoginCapability(
            new ArrayAuthentication(['x' => 'y'])
        );

        $session = $capability->manifest($connection, new Session());
        $this->assertSame(Session::STATE_CONNECTED, $session->getState());
    }
}
