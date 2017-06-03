<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Protocol\Smtp\Negotiation;

use Genkgo\Mail\Exception\ConnectionInsecureException;
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

        $negotiator = new ForceTlsUpgradeNegotiation($connection, 'hostname', CryptoConstant::TYPE_BEST_PRACTISE);
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

        $negotiator = new ForceTlsUpgradeNegotiation($connection, 'hostname', CryptoConstant::TYPE_BEST_PRACTISE);
        $negotiator->negotiate(new Client($connection));
    }
}