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
    public function it_uses_advertised()
    {
        $connection = new FakeSmtpConnection();
        $connection->connect();

        $negotiator = new AuthNegotiation('hostname', Client::AUTH_AUTO, 'user', 'pass');
        $negotiator->negotiate(new Client($connection));

        $this->assertArraySubset(
            ['state' => true, 'username' => 'user', 'password' => 'pass', 'method' => 'LOGIN'],
            $connection->getMetaData()['auth']
        );
    }

    /**
     * @test
     */
    public function it_uses_login()
    {
        $connection = new FakeSmtpConnection();
        $connection->connect();

        $negotiator = new AuthNegotiation('hostname', Client::AUTH_LOGIN, 'user', 'pass');
        $negotiator->negotiate(new Client($connection));

        $this->assertArraySubset(
            ['state' => true, 'username' => 'user', 'password' => 'pass', 'method' => 'LOGIN'],
            $connection->getMetaData()['auth']
        );
    }

    /**
     * @test
     */
    public function it_uses_plain()
    {
        $connection = new FakeSmtpConnection();
        $connection->connect();

        $negotiator = new AuthNegotiation('hostname', Client::AUTH_PLAIN, 'user', 'pass');
        $negotiator->negotiate(new Client($connection));

        $this->assertArraySubset(
            ['state' => true, 'username' => 'user', 'password' => 'pass', 'method' => 'PLAIN'],
            $connection->getMetaData()['auth']
        );
    }

    /**
     * @test
     */
    public function it_will_throw_when_not_advertised()
    {
        $this->expectException(SmtpAuthenticationException::class);

        $connection = new FakeSmtpConnection(['250-AUTH XXX']);
        $connection->connect();

        $negotiator = new AuthNegotiation('hostname', Client::AUTH_AUTO, 'user', 'pass');
        $negotiator->negotiate(new Client($connection));
    }
}
