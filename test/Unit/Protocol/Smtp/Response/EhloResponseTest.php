<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Protocol\Smtp\Response;

use Genkgo\Mail\Protocol\ConnectionInterface;
use Genkgo\Mail\Protocol\Smtp\Client;
use Genkgo\Mail\Protocol\Smtp\Reply;
use Genkgo\Mail\Protocol\Smtp\Response\EhloResponse;
use Genkgo\TestMail\AbstractTestCase;

final class EhloResponseTest extends AbstractTestCase
{

    /**
     * @test
     */
    public function it_sends_advertisements()
    {
        $connection = $this->createMock(ConnectionInterface::class);

        $reply = (new Reply(new Client($connection)))
            ->withLine(250, 'hello')
            ->withLine(250, 'STARTTLS');

        $response = new EhloResponse($reply);
        $this->assertTrue($response->isAdvertising('STARTTLS'));
    }

    /**
     * @test
     */
    public function it_sends_advertisements_with_parameters()
    {
        $connection = $this->createMock(ConnectionInterface::class);

        $reply = (new Reply(new Client($connection)))
            ->withLine(250, 'hello')
            ->withLine(250, 'AUTH LOGIN PLAIN');

        $response = new EhloResponse($reply);
        $this->assertTrue($response->isAdvertising('AUTH LOGIN'));
        $this->assertTrue($response->isAdvertising('AUTH PLAIN'));
        $this->assertFalse($response->isAdvertising('XYZ'));
        $this->assertFalse($response->isAdvertising(''));
    }

}