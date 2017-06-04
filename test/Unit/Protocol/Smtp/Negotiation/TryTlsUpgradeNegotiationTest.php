<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Protocol\Smtp\Negotiation;

use Genkgo\Mail\Protocol\CryptoConstant;
use Genkgo\Mail\Protocol\Smtp\Client;
use Genkgo\Mail\Protocol\Smtp\Negotiation\TryTlsUpgradeNegotiation;
use Genkgo\TestMail\AbstractTestCase;
use Genkgo\TestMail\Stub\FakeSmtpConnection;

final class TryTlsUpgradeNegotiationTest extends AbstractTestCase
{

    /**
     * @test
     */
    public function it_tries_to_upgrade_when_not_secure()
    {
        $connection = new FakeSmtpConnection();
        $connection->connect();

        $negotiator = new TryTlsUpgradeNegotiation(
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
    public function it_continues_when_not_secure_allowed()
    {
        $connection = new FakeSmtpConnection(['250 AUTH PLAIN']);
        $connection->connect();

        $negotiator = new TryTlsUpgradeNegotiation(
            $connection,
            'hostname',
            CryptoConstant::getDefaultMethod(PHP_VERSION)
        );
        $negotiator->negotiate(new Client($connection));

        $this->assertArrayNotHasKey('crypto', $connection->getMetaData());
    }

}