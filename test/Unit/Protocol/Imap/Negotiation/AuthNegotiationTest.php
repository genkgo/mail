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
    public function it_emits_auth_plain(): void
    {
        $connection = $this->createMock(ConnectionInterface::class);
        $connection
            ->expects($this->exactly(1))
            ->method('addListener');

        $connection
            ->expects($this->exactly(2))
            ->method('send')
            ->withConsecutive(
                ["TAG1 AUTHENTICATE PLAIN\r\n"],
                ["AHVzZXJuYW1lAHBhc3N3b3Jk\r\n"]
            );

        $connection
            ->expects($this->exactly(2))
            ->method('receive')
            ->willReturnOnConsecutiveCalls(
                '+ Send password',
                'TAG1 OK'
            );

        $client = new Client($connection, new GeneratorTagFactory(), []);

        $negotiation = new AuthNegotiation(Client::AUTH_PLAIN, 'username', 'password');
        $negotiation->negotiate($client);
    }

    /**
     * @test
     */
    public function it_uses_capability_when_method_is_auto(): void
    {
        $connection = $this->createMock(ConnectionInterface::class);
        $connection
            ->expects($this->exactly(1))
            ->method('addListener');

        $connection
            ->expects($this->exactly(3))
            ->method('send')
            ->withConsecutive(
                ["TAG1 CAPABILITY\r\n"],
                ["TAG2 AUTHENTICATE PLAIN\r\n"],
                ["AHVzZXJuYW1lAHBhc3N3b3Jk\r\n"]
            );

        $connection
            ->expects($this->exactly(4))
            ->method('receive')
            ->willReturnOnConsecutiveCalls(
                '* CAPABILITY IMAP4rev1 STARTTLS AUTH=PLAIN',
                'TAG1 OK',
                '+ Send password',
                'TAG2 OK'
            );

        $client = new Client($connection, new GeneratorTagFactory(), []);

        $negotiation = new AuthNegotiation(Client::AUTH_AUTO, 'username', 'password');
        $negotiation->negotiate($client);
    }

    /**
     * @test
     */
    public function it_requests_capabilities_when_method_is_auto_and_uses_login_when_not_otherwise_advertised(): void
    {
        $connection = $this->createMock(ConnectionInterface::class);
        $connection
            ->expects($this->exactly(1))
            ->method('addListener');

        $connection
            ->expects($this->exactly(2))
            ->method('send')
            ->withConsecutive(
                ["TAG1 CAPABILITY\r\n"],
                ["TAG2 LOGIN username password\r\n"],
            );

        $connection
            ->expects($this->exactly(3))
            ->method('receive')
            ->willReturnOnConsecutiveCalls(
                '* CAPABILITY IMAP4rev1 STARTTLS',
                'TAG1 OK',
                'TAG2 OK'
            );

        $client = new Client($connection, new GeneratorTagFactory(), []);

        $negotiation = new AuthNegotiation(Client::AUTH_AUTO, 'username', 'password');
        $negotiation->negotiate($client);
    }

    /**
     * @test
     */
    public function it_uses_capability_when_method_is_auto_and_not_advertised_and_login_disabled(): void
    {
        $this->expectException(ImapAuthenticationException::class);

        $connection = $this->createMock(ConnectionInterface::class);
        $connection
            ->expects($this->exactly(1))
            ->method('addListener');

        $connection
            ->expects($this->exactly(1))
            ->method('send')
            ->with("TAG1 CAPABILITY\r\n");

        $connection
            ->expects($this->exactly(2))
            ->method('receive')
            ->willReturnOnConsecutiveCalls(
                '* CAPABILITY IMAP4rev1 STARTTLS LOGINDISABLED',
                'TAG1 OK'
            );
        $client = new Client($connection, new GeneratorTagFactory(), []);

        $negotiation = new AuthNegotiation(Client::AUTH_AUTO, 'username', 'password');
        $negotiation->negotiate($client);
    }

    /**
     * @test
     */
    public function it_throws_auth_exception_when_plain_auth_fails(): void
    {
        $this->expectException(ImapAuthenticationException::class);
        $this->expectExceptionMessage('Failed to authenticate: NO [AUTHENTICATIONFAILED] Authentication failed.');

        $connection = $this->createMock(ConnectionInterface::class);
        $connection
            ->expects($this->exactly(1))
            ->method('addListener');

        $connection
            ->expects($this->exactly(2))
            ->method('send')
            ->withConsecutive(
                ["TAG1 AUTHENTICATE PLAIN\r\n"],
                ["AHVzZXJuYW1lAHBhc3N3b3Jk\r\n"]
            );

        $connection
            ->expects($this->exactly(2))
            ->method('receive')
            ->willReturnOnConsecutiveCalls(
                '+ Send password',
                'TAG1 NO [AUTHENTICATIONFAILED] Authentication failed.'
            );

        $client = new Client($connection, new GeneratorTagFactory(), []);

        $negotiation = new AuthNegotiation(Client::AUTH_PLAIN, 'username', 'password');
        $negotiation->negotiate($client);
    }

    /**
     * @test
     */
    public function it_throws_auth_exception_when_login_fails(): void
    {
        $this->expectException(ImapAuthenticationException::class);
        $this->expectExceptionMessage('Failed to authenticate: NO [AUTHENTICATIONFAILED] Authentication failed.');

        $connection = $this->createMock(ConnectionInterface::class);
        $connection
            ->expects($this->exactly(1))
            ->method('addListener');

        $connection
            ->expects($this->exactly(2))
            ->method('send')
            ->withConsecutive(
                ["TAG1 CAPABILITY\r\n"],
                ["TAG2 LOGIN username password\r\n"]
            );

        $connection
            ->expects($this->exactly(3))
            ->method('receive')
            ->willReturnOnConsecutiveCalls(
                '* CAPABILITY IMAP4rev1 STARTTLS',
                'TAG1 OK',
                'TAG2 NO [AUTHENTICATIONFAILED] Authentication failed.'
            );

        $client = new Client($connection, new GeneratorTagFactory(), []);

        $negotiation = new AuthNegotiation(Client::AUTH_AUTO, 'username', 'password');
        $negotiation->negotiate($client);

        $client = new Client($connection, new GeneratorTagFactory(), []);

        $negotiation = new AuthNegotiation(Client::AUTH_PLAIN, 'username', 'password');
        $negotiation->negotiate($client);
    }
}
