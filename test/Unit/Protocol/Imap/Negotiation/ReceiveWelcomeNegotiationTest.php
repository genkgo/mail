<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Protocol\Imap\Negotiation;

use Genkgo\Mail\Protocol\ConnectionInterface;
use Genkgo\Mail\Protocol\Imap\Client;
use Genkgo\Mail\Protocol\Imap\Negotiation\ReceiveWelcomeNegotiation;
use Genkgo\Mail\Protocol\Imap\TagFactory\GeneratorTagFactory;
use Genkgo\TestMail\AbstractTestCase;

final class ReceiveWelcomeNegotiationTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_emits_auth_plain()
    {
        $connection = $this->createMock(ConnectionInterface::class);
        $connection
            ->expects($this->at(0))
            ->method('addListener');

        $connection
            ->expects($this->at(1))
            ->method('receive')
            ->willReturn("* OK IMAP4rev1 Service Ready\r\n");

        $client = new Client($connection, new GeneratorTagFactory(), []);

        $negotiation = new ReceiveWelcomeNegotiation($connection);
        $negotiation->negotiate($client);
    }
}
