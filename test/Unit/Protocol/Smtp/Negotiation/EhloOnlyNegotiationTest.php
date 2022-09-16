<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Protocol\Smtp\Negotiation;

use Genkgo\Mail\Protocol\ConnectionInterface;
use Genkgo\Mail\Protocol\CryptoConstant;
use Genkgo\Mail\Protocol\Smtp\Client;
use Genkgo\Mail\Protocol\Smtp\Negotiation\EhloOnlyNegotiation;
use Genkgo\Mail\Protocol\Smtp\Negotiation\TryTlsUpgradeNegotiation;
use Genkgo\TestMail\AbstractTestCase;
use Genkgo\TestMail\Stub\FakeSmtpConnection;

final class EhloOnlyNegotiationTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_tries_to_upgrade_when_not_secure(): void
    {
        $connection = $this->createMock(ConnectionInterface::class);

        $connection
            ->expects($this->exactly(1))
            ->method('addListener');

        $connection
            ->expects($this->exactly(1))
            ->method('receive')
            ->willReturn("250 OK\r\n");

        $negotiator = new EhloOnlyNegotiation($connection, 'hostname');
        $negotiator->negotiate(new Client($connection));
    }
}
