<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Protocol\Imap\Negotiation;

use Genkgo\Mail\Exception\ImapAuthenticationException;
use Genkgo\Mail\Protocol\ConnectionInterface;
use Genkgo\Mail\Protocol\Imap\Client;
use Genkgo\Mail\Protocol\Imap\Negotiation\AuthNegotiation;
use Genkgo\Mail\Protocol\Imap\TagFactory\GeneratorTagFactory;
use Genkgo\TestMail\AbstractTestCase;

final class AuthNegotiationTest extends AbstractTestCase
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
            ->method('send')
            ->with("TAG1 AUTHENTICATE PLAIN\r\n");

        $connection
            ->expects($this->at(2))
            ->method('receive')
            ->willReturn('+ Send password');

        $connection
            ->expects($this->at(3))
            ->method('send')
            ->with("AHVzZXJuYW1lAHBhc3N3b3Jk\r\n");

        $connection
            ->expects($this->at(4))
            ->method('receive')
            ->willReturn('TAG1 OK');

        $client = new Client($connection, new GeneratorTagFactory(), []);

        $negotiation = new AuthNegotiation(Client::AUTH_PLAIN, 'username', 'password');
        $negotiation->negotiate($client);
    }

    /**
     * @test
     */
    public function it_uses_capability_when_method_is_auto()
    {
        $connection = $this->createMock(ConnectionInterface::class);
        $connection
            ->expects($this->at(0))
            ->method('addListener');

        $connection
            ->expects($this->at(1))
            ->method('send')
            ->with("TAG1 CAPABILITY\r\n");

        $connection
            ->expects($this->at(2))
            ->method('receive')
            ->willReturn('* CAPABILITY IMAP4rev1 STARTTLS AUTH=PLAIN');

        $connection
            ->expects($this->at(3))
            ->method('receive')
            ->willReturn('TAG1 OK');

        $connection
            ->expects($this->at(4))
            ->method('send')
            ->with("TAG2 AUTHENTICATE PLAIN\r\n");

        $connection
            ->expects($this->at(5))
            ->method('receive')
            ->willReturn('+ Send password');

        $connection
            ->expects($this->at(6))
            ->method('send')
            ->with("AHVzZXJuYW1lAHBhc3N3b3Jk\r\n");

        $connection
            ->expects($this->at(7))
            ->method('receive')
            ->willReturn('TAG2 OK');

        $client = new Client($connection, new GeneratorTagFactory(), []);

        $negotiation = new AuthNegotiation(Client::AUTH_AUTO, 'username', 'password');
        $negotiation->negotiate($client);
    }

    /**
     * @test
     */
    public function it_requests_capabilities_when_method_is_auto_and_uses_login_when_not_otherwise_advertised()
    {
        $connection = $this->createMock(ConnectionInterface::class);
        $connection
            ->expects($this->at(0))
            ->method('addListener');

        $connection
            ->expects($this->at(1))
            ->method('send')
            ->with("TAG1 CAPABILITY\r\n");

        $connection
            ->expects($this->at(2))
            ->method('receive')
            ->willReturn('* CAPABILITY IMAP4rev1 STARTTLS');

        $connection
            ->expects($this->at(3))
            ->method('receive')
            ->willReturn('TAG1 OK');

        $connection
            ->expects($this->at(4))
            ->method('send')
            ->with("TAG2 LOGIN username password\r\n");

        $connection
            ->expects($this->at(5))
            ->method('receive')
            ->willReturn('TAG2 OK');


        $client = new Client($connection, new GeneratorTagFactory(), []);

        $negotiation = new AuthNegotiation(Client::AUTH_AUTO, 'username', 'password');
        $negotiation->negotiate($client);
    }

    /**
     * @test
     */
    public function it_uses_capability_when_method_is_auto_and_not_advertised_and_login_disabled()
    {
        $this->expectException(ImapAuthenticationException::class);

        $connection = $this->createMock(ConnectionInterface::class);
        $connection
            ->expects($this->at(0))
            ->method('addListener');

        $connection
            ->expects($this->at(1))
            ->method('send')
            ->with("TAG1 CAPABILITY\r\n");

        $connection
            ->expects($this->at(2))
            ->method('receive')
            ->willReturn('* CAPABILITY IMAP4rev1 STARTTLS LOGINDISABLED');

        $connection
            ->expects($this->at(3))
            ->method('receive')
            ->willReturn('TAG1 OK');

        $client = new Client($connection, new GeneratorTagFactory(), []);

        $negotiation = new AuthNegotiation(Client::AUTH_AUTO, 'username', 'password');
        $negotiation->negotiate($client);
    }
}
