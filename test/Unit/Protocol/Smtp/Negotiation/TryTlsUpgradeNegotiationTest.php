<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Protocol\Smtp\Negotiation;

use Genkgo\Mail\Exception\SecureConnectionUpgradeException;
use Genkgo\Mail\Protocol\ConnectionInterface;
use Genkgo\Mail\Protocol\CryptoConstant;
use Genkgo\Mail\Protocol\Smtp\Client;
use Genkgo\Mail\Protocol\Smtp\Negotiation\TryTlsUpgradeNegotiation;
use Genkgo\TestMail\AbstractTestCase;
use Genkgo\TestMail\Stub\FakeSmtpConnection;

final class TryTlsUpgradeNegotiationTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_tries_to_upgrade_when_not_secure(): void
    {
        $connection = new FakeSmtpConnection();
        $connection->connect();

        $negotiator = new TryTlsUpgradeNegotiation(
            $connection,
            'hostname',
            CryptoConstant::getDefaultMethod(PHP_VERSION)
        );
        $negotiator->negotiate(new Client($connection));

        $this->assertTrue($connection->getMetaData()['crypto']);
    }

    /**
     * @test
     */
    public function it_continues_when_not_secure_allowed(): void
    {
        $connection = new FakeSmtpConnection(['250 AUTH PLAIN']);
        $connection->connect();

        $negotiator = new TryTlsUpgradeNegotiation(
            $connection,
            'hostname',
            CryptoConstant::getDefaultMethod(PHP_VERSION)
        );
        $negotiator->negotiate(new Client($connection));

        $this->assertArrayNotHasKey('crypto', $connection->getMetaData());
    }

    /**
     * @test
     */
    public function it_uses_legacy_rfc821_helo_when_ehlo_not_supported(): void
    {
        $connection = FakeSmtpConnection::newLegacyRfc821();
        $connection->connect();

        $negotiator = new TryTlsUpgradeNegotiation(
            $connection,
            'hostname',
            CryptoConstant::getDefaultMethod(PHP_VERSION)
        );
        $negotiator->negotiate(new Client($connection));

        $this->assertTrue($connection->getMetaData()['crypto']);
    }

    /**
     * @test
     */
    public function it_does_not_throw_when_using_legacy_rfc821_and_starttls_not_supported(): void
    {
        $connection = $this->createMock(ConnectionInterface::class);

        $connection
            ->expects($this->exactly(1))
            ->method('addListener');

        $connection
            ->expects($this->exactly(1))
            ->method('getMetaData')
            ->willReturn([]);

        $connection
            ->expects($this->exactly(3))
            ->method('send')
            ->withConsecutive(
                ["EHLO hostname\r\n"],
                ["HELO hostname\r\n"],
                ["STARTTLS\r\n"]
            )
            ->willReturn(16);

        $connection
            ->expects($this->exactly(3))
            ->method('receive')
            ->willReturnOnConsecutiveCalls(
                "502 Not implemented\r\n",
                "220 Hello\r\n",
                "502 Not implemented\r\n"
            );

        $negotiator = new TryTlsUpgradeNegotiation(
            $connection,
            'hostname',
            CryptoConstant::getDefaultMethod(PHP_VERSION)
        );
        $negotiator->negotiate(new Client($connection));
    }

    /**
     * @test
     */
    public function it_does_not_upgrade_when_already_crypto(): void
    {
        $connection = $this->createMock(ConnectionInterface::class);

        $connection
            ->expects($this->exactly(1))
            ->method('addListener');

        $connection
            ->expects($this->exactly(1))
            ->method('getMetaData')
            ->willReturn(['crypto' => []]);

        $negotiator = new TryTlsUpgradeNegotiation(
            $connection,
            'hostname',
            CryptoConstant::getDefaultMethod(PHP_VERSION)
        );
        $negotiator->negotiate(new Client($connection));
    }

    /**
     * @test
     */
    public function it_does_throw_when_starttls_failed(): void
    {
        $connection = $this->createMock(ConnectionInterface::class);

        $connection
            ->expects($this->exactly(1))
            ->method('addListener');

        $connection
            ->expects($this->exactly(1))
            ->method('getMetaData')
            ->willReturn([]);

        $connection
            ->expects($this->exactly(2))
            ->method('send')
            ->withConsecutive(
                ["EHLO hostname\r\n"],
                ["STARTTLS\r\n"]
            )
            ->willReturn(16);

        $connection
            ->expects($this->exactly(3))
            ->method('receive')
            ->willReturnOnConsecutiveCalls(
                "250-Welcome\r\n",
                "250 STARTTLS\r\n",
                "250 OK\r\n"
            );

        $connection
            ->expects($this->exactly(1))
            ->method('upgrade')
            ->willThrowException(new SecureConnectionUpgradeException());

        $this->expectException(SecureConnectionUpgradeException::class);

        $negotiator = new TryTlsUpgradeNegotiation(
            $connection,
            'hostname',
            CryptoConstant::getDefaultMethod(PHP_VERSION)
        );
        $negotiator->negotiate(new Client($connection));
    }

    /**
     * @test
     */
    public function it_does_throw_when_using_legacy_rfc821_and_starttls_failed(): void
    {
        $connection = $this->createMock(ConnectionInterface::class);

        $connection
            ->expects($this->exactly(1))
            ->method('addListener');

        $connection
            ->expects($this->exactly(1))
            ->method('getMetaData')
            ->willReturn([]);

        $connection
            ->expects($this->exactly(3))
            ->method('send')
            ->withConsecutive(
                ["EHLO hostname\r\n"],
                ["HELO hostname\r\n"],
                ["STARTTLS\r\n"]
            )
            ->willReturn(16);

        $connection
            ->expects($this->exactly(3))
            ->method('receive')
            ->willReturnOnConsecutiveCalls(
                "502 Not implemented\r\n",
                "220 Hello\r\n",
                "250 OK\r\n"
            );

        $connection
            ->expects($this->exactly(1))
            ->method('upgrade')
            ->willThrowException(new SecureConnectionUpgradeException());

        $this->expectException(SecureConnectionUpgradeException::class);

        $negotiator = new TryTlsUpgradeNegotiation(
            $connection,
            'hostname',
            CryptoConstant::getDefaultMethod(PHP_VERSION)
        );
        $negotiator->negotiate(new Client($connection));
    }
}
