<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Protocol\Smtp\Negotiation;

use Genkgo\Mail\Exception\SmtpAuthenticationException;
use Genkgo\Mail\Protocol\Smtp\Client;
use Genkgo\Mail\Protocol\Smtp\Negotiation\AuthNegotiation;
use Genkgo\TestMail\AbstractTestCase;
use Genkgo\TestMail\Stub\FakeSmtpConnection;

final class AuthNegotiationTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_uses_advertised(): void
    {
        $connection = new FakeSmtpConnection();
        $connection->connect();

        $negotiator = new AuthNegotiation('hostname', Client::AUTH_AUTO, 'user', 'pass');
        $negotiator->negotiate(new Client($connection));

        $advertised = $connection->getMetaData()['auth'];
        foreach (['state' => true, 'username' => 'user', 'password' => 'pass', 'method' => 'LOGIN'] as $key => $value) {
            $this->assertSame($value, $advertised[$key]);
        }
    }

    /**
     * @test
     */
    public function it_uses_login(): void
    {
        $connection = new FakeSmtpConnection();
        $connection->connect();

        $negotiator = new AuthNegotiation('hostname', Client::AUTH_LOGIN, 'user', 'pass');
        $negotiator->negotiate(new Client($connection));

        $advertised = $connection->getMetaData()['auth'];
        foreach (['state' => true, 'username' => 'user', 'password' => 'pass', 'method' => 'LOGIN'] as $key => $value) {
            $this->assertSame($value, $advertised[$key]);
        }
    }

    /**
     * @test
     */
    public function it_uses_plain(): void
    {
        $connection = new FakeSmtpConnection();
        $connection->connect();

        $negotiator = new AuthNegotiation('hostname', Client::AUTH_PLAIN, 'user', 'pass');
        $negotiator->negotiate(new Client($connection));

        $advertised = $connection->getMetaData()['auth'];
        foreach (['state' => true, 'username' => 'user', 'password' => 'pass', 'method' => 'PLAIN'] as $key => $value) {
            $this->assertSame($value, $advertised[$key]);
        }
    }

    /**
     * @test
     */
    public function it_will_throw_when_not_advertised(): void
    {
        $this->expectException(SmtpAuthenticationException::class);

        $connection = new FakeSmtpConnection(['250-AUTH XXX']);
        $connection->connect();

        $negotiator = new AuthNegotiation('hostname', Client::AUTH_AUTO, 'user', 'pass');
        $negotiator->negotiate(new Client($connection));
    }
}
