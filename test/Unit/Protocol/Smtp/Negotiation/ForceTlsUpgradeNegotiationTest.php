<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Protocol\Smtp\Negotiation;

use Genkgo\Mail\Exception\ConnectionInsecureException;
use Genkgo\Mail\Protocol\ConnectionInterface;
use Genkgo\Mail\Protocol\CryptoConstant;
use Genkgo\Mail\Protocol\Smtp\Client;
use Genkgo\Mail\Protocol\Smtp\Negotiation\ForceTlsUpgradeNegotiation;
use Genkgo\TestMail\AbstractTestCase;
use Genkgo\TestMail\Stub\FakeSmtpConnection;

final class ForceTlsUpgradeNegotiationTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_tries_to_upgrade_when_not_secure()
    {
        $connection = new FakeSmtpConnection();
        $connection->connect();

        $negotiator = new ForceTlsUpgradeNegotiation(
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
    public function it_throw_when_not_secure()
    {
        $this->expectException(ConnectionInsecureException::class);

        $connection = new FakeSmtpConnection(['250 AUTH PLAIN']);
        $connection->connect();

        $negotiator = new ForceTlsUpgradeNegotiation(
            $connection,
            'hostname',
            CryptoConstant::getDefaultMethod(PHP_VERSION)
        );
        $negotiator->negotiate(new Client($connection));
    }

    /**
     * @test
     */
    public function it_uses_legacy_rfc821_helo_when_ehlo_not_supported()
    {
        $connection = FakeSmtpConnection::newLegacyRfc821();
        $connection->connect();

        $negotiator = new ForceTlsUpgradeNegotiation(
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
    public function it_throws_when_using_legacy_rfc821_and_starttls_not_supported()
    {
        $this->expectException(ConnectionInsecureException::class);

        $connection = $this->createMock(ConnectionInterface::class);

        $connection
            ->expects($this->at(0))
            ->method('addListener');

        $connection
            ->expects($this->at(1))
            ->method('getMetaData')
            ->willReturn([]);

        $connection
            ->expects($this->at(2))
            ->method('send')
            ->with("EHLO hostname\r\n")
            ->willReturn(16);

        $connection
            ->expects($this->at(3))
            ->method('receive')
            ->willReturn("502 Not implemented\r\n");

        $connection
            ->expects($this->at(4))
            ->method('send')
            ->with("HELO hostname\r\n")
            ->willReturn(16);

        $connection
            ->expects($this->at(5))
            ->method('receive')
            ->willReturn("220 Hello\r\n");

        $connection
            ->expects($this->at(6))
            ->method('send')
            ->with("STARTTLS\r\n")
            ->willReturn(16);

        $connection
            ->expects($this->at(7))
            ->method('receive')
            ->willReturn("502 Not implemented\r\n");

        $negotiator = new ForceTlsUpgradeNegotiation(
            $connection,
            'hostname',
            CryptoConstant::getDefaultMethod(PHP_VERSION)
        );
        $negotiator->negotiate(new Client($connection));
    }

    /**
     * @test
     */
    public function it_does_not_upgrade_when_already_crypto()
    {
        $connection = $this->createMock(ConnectionInterface::class);

        $connection
            ->expects($this->at(0))
            ->method('addListener');

        $connection
            ->expects($this->at(1))
            ->method('getMetaData')
            ->willReturn(['crypto' => []]);

        $negotiator = new ForceTlsUpgradeNegotiation(
            $connection,
            'hostname',
            CryptoConstant::getDefaultMethod(PHP_VERSION)
        );
        $negotiator->negotiate(new Client($connection));
    }
}
