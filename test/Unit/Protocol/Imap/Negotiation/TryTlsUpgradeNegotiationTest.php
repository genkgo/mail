<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Protocol\Imap\Negotiation;

use Genkgo\Mail\Protocol\ConnectionInterface;
use Genkgo\Mail\Protocol\CryptoConstant;
use Genkgo\Mail\Protocol\Imap\Client;
use Genkgo\Mail\Protocol\Imap\Negotiation\TryTlsUpgradeNegotiation;
use Genkgo\Mail\Protocol\Imap\TagFactory\GeneratorTagFactory;
use Genkgo\TestMail\AbstractTestCase;

final class TryTlsUpgradeNegotiationTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_upgrades_if_advertised(): void
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
                ["TAG1 CAPABILITY\r\n"],
                ["TAG2 STARTTLS\r\n"]
            );

        $connection
            ->expects($this->exactly(3))
            ->method('receive')
            ->willReturnOnConsecutiveCalls(
                '* CAPABILITY IMAP4rev1 STARTTLS AUTH=PLAIN',
                'TAG1 OK',
                'TAG2 OK'
            );

        $connection
            ->expects($this->exactly(1))
            ->method('upgrade');

        $client = new Client($connection, new GeneratorTagFactory(), []);

        $negotiation = new TryTlsUpgradeNegotiation(
            $connection,
            CryptoConstant::getDefaultMethod(PHP_VERSION)
        );
        $negotiation->negotiate($client);
    }

    /**
     * @test
     */
    public function it_does_not_throw_if_not_advertised(): void
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
            ->expects($this->exactly(1))
            ->method('send')
            ->with("TAG1 CAPABILITY\r\n");

        $connection
            ->expects($this->exactly(2))
            ->method('receive')
            ->willReturnOnConsecutiveCalls(
                '* CAPABILITY IMAP4rev1 AUTH=PLAIN',
                'TAG1 OK'
            );

        $client = new Client($connection, new GeneratorTagFactory(), []);

        $negotiation = new TryTlsUpgradeNegotiation(
            $connection,
            CryptoConstant::getDefaultMethod(PHP_VERSION)
        );
        $negotiation->negotiate($client);
    }

    /**
     * @test
     */
    public function it_does_not_upgrade_when_already_encrypted(): void
    {
        $connection = $this->createMock(ConnectionInterface::class);
        $connection
            ->expects($this->exactly(1))
            ->method('addListener');

        $connection
            ->expects($this->exactly(1))
            ->method('getMetaData')
            ->willReturn(['crypto' => []]);

        $client = new Client($connection, new GeneratorTagFactory(), []);

        $negotiation = new TryTlsUpgradeNegotiation(
            $connection,
            CryptoConstant::getDefaultMethod(PHP_VERSION)
        );

        $negotiation->negotiate($client);
    }
}
