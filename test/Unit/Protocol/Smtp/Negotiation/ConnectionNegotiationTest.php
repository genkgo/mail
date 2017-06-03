<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Protocol\Smtp;

use Genkgo\Mail\Exception\ConnectionInsecureException;
use Genkgo\Mail\Protocol\Smtp\Client;
use Genkgo\Mail\Protocol\Smtp\Negotiation\ConnectionNegotiation;
use Genkgo\TestMail\AbstractTestCase;
use Genkgo\TestMail\Stub\FakeSmtpConnection;

final class ConnectionNegotiationTest extends AbstractTestCase
{

    /**
     * @test
     */
    public function it_tries_to_upgrade_when_not_secure()
    {
        $connection = new FakeSmtpConnection();
        $connection->connect();

        $negotiator = new ConnectionNegotiation($connection, 'hostname', false);
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

        $negotiator = new ConnectionNegotiation($connection, 'hostname', false);
        $negotiator->negotiate(new Client($connection));
    }

    /**
     * @test
     */
    public function it_continues_when_not_secure_allowed()
    {
        $connection = new FakeSmtpConnection(['250 AUTH PLAIN']);
        $connection->connect();

        $negotiator = new ConnectionNegotiation($connection, 'hostname', true);
        $negotiator->negotiate(new Client($connection));

        $this->assertArrayNotHasKey('crypto', $connection->getMetaData());
    }

}