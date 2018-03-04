<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Protocol\Imap\Negotiation;

use Genkgo\Mail\Exception\ConnectionInsecureException;
use Genkgo\Mail\Protocol\ConnectionInterface;
use Genkgo\Mail\Protocol\CryptoConstant;
use Genkgo\Mail\Protocol\Imap\Client;
use Genkgo\Mail\Protocol\Imap\Negotiation\ForceTlsUpgradeNegotiation;
use Genkgo\Mail\Protocol\Imap\TagFactory\GeneratorTagFactory;
use Genkgo\TestMail\AbstractTestCase;

final class ForceTlsUpgradeNegotiationTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_upgrades_if_advertised()
    {
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
            ->with("TAG1 CAPABILITY\r\n");

        $connection
            ->expects($this->at(3))
            ->method('receive')
            ->willReturn('* CAPABILITY IMAP4rev1 STARTTLS AUTH=PLAIN');

        $connection
            ->expects($this->at(4))
            ->method('receive')
            ->willReturn('TAG1 OK');

        $connection
            ->expects($this->at(5))
            ->method('send')
            ->with("TAG2 STARTTLS\r\n");

        $connection
            ->expects($this->at(6))
            ->method('receive')
            ->willReturn('TAG2 OK');

        $connection
            ->expects($this->at(7))
            ->method('upgrade');

        $connection
            ->expects($this->at(8))
            ->method('getMetaData')
            ->willReturn(['crypto' => []]);

        $client = new Client($connection, new GeneratorTagFactory(), []);

        $negotiation = new ForceTlsUpgradeNegotiation(
            $connection,
            CryptoConstant::getDefaultMethod(PHP_VERSION)
        );
        $negotiation->negotiate($client);
    }

    /**
     * @test
     */
    public function it_throws_if_not_advertised()
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
            ->with("TAG1 CAPABILITY\r\n");

        $connection
            ->expects($this->at(3))
            ->method('receive')
            ->willReturn('* CAPABILITY IMAP4rev1 AUTH=PLAIN');

        $connection
            ->expects($this->at(4))
            ->method('receive')
            ->willReturn('TAG1 OK');

        $connection
            ->expects($this->at(5))
            ->method('getMetaData')
            ->willReturn([]);

        $client = new Client($connection, new GeneratorTagFactory(), []);

        $negotiation = new ForceTlsUpgradeNegotiation(
            $connection,
            CryptoConstant::getDefaultMethod(PHP_VERSION)
        );
        $negotiation->negotiate($client);
    }

    /**
     * @test
     */
    public function it_does_not_upgrade_when_already_encrypted()
    {
        $connection = $this->createMock(ConnectionInterface::class);
        $connection
            ->expects($this->at(0))
            ->method('addListener');

        $connection
            ->expects($this->at(1))
            ->method('getMetaData')
            ->willReturn(['crypto' => []]);

        $client = new Client($connection, new GeneratorTagFactory(), []);

        $negotiation = new ForceTlsUpgradeNegotiation(
            $connection,
            CryptoConstant::getDefaultMethod(PHP_VERSION)
        );

        $negotiation->negotiate($client);
    }
}
