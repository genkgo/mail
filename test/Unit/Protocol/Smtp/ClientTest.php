<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Protocol\Smtp;

use Genkgo\Mail\Protocol\Smtp\Client;
use Genkgo\Mail\Protocol\Smtp\Request\EhloCommand;
use Genkgo\Mail\Protocol\Smtp\Request\NoopCommand;
use Genkgo\TestMail\AbstractTestCase;
use Genkgo\TestMail\Stub\FakeSmtpConnection;

final class ClientTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_creates_a_reply()
    {
        $connection = new FakeSmtpConnection();
        $connection->connect();

        $command = new NoopCommand();

        $client = new Client($connection);
        $reply = $client->request($command);

        $this->assertEquals(['OK'], $reply->getMessages());
    }

    /**
     * @test
     */
    public function it_creates_a_reply_with_multiple_lines()
    {
        $connection = new FakeSmtpConnection();
        $connection->connect();

        $command = new EhloCommand('host');

        $client = new Client($connection);
        $reply = $client->request($command);

        $this->assertEquals(
            ['welcome to fake connection', 'STARTTLS', 'AUTH PLAIN', 'AUTH LOGIN'],
            $reply->getMessages()
        );
    }
}
