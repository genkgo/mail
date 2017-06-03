<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Protocol\Smtp;

use Genkgo\Mail\Exception\AssertionFailedException;
use Genkgo\Mail\Protocol\ConnectionInterface;
use Genkgo\Mail\Protocol\Smtp\Client;
use Genkgo\Mail\Protocol\Smtp\Reply;
use Genkgo\TestMail\AbstractTestCase;

final class ReplyTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_has_messages()
    {
        $connection = $this->createMock(ConnectionInterface::class);

        $reply = (new Reply(new Client($connection)))
            ->withLine(250, 'hello')
            ->withLine(250, 'STARTTLS');

        $this->assertFalse($reply->isError());
        $this->assertEquals(['hello', 'STARTTLS'], $reply->getMessages());
    }

    /**
     * @test
     */
    public function it_asserts()
    {
        $connection = $this->createMock(ConnectionInterface::class);
        $client = new Client($connection);

        $reply = (new Reply($client))
            ->withLine(250, 'hello')
            ->withLine(250, 'STARTTLS');

        $this->assertFalse($reply->isError());
        $this->assertEquals($client, $reply->assert(250));
    }

    /**
     * @test
     */
    public function it_asserts_completed()
    {
        $connection = $this->createMock(ConnectionInterface::class);
        $client = new Client($connection);

        $reply = (new Reply($client))
            ->withLine(250, 'hello')
            ->withLine(250, 'STARTTLS');

        $this->assertFalse($reply->isError());
        $this->assertEquals($client, $reply->assertCompleted());
    }

    /**
     * @test
     */
    public function it_asserts_intermediate()
    {
        $connection = $this->createMock(ConnectionInterface::class);
        $client = new Client($connection);

        $reply = (new Reply($client))
            ->withLine(354, 'send data');

        $this->assertFalse($reply->isError());
        $this->assertEquals($client, $reply->assertIntermediate());
    }

    /**
     * @test
     */
    public function it_throws_when_making_wrong_assertions()
    {
        $this->expectException(AssertionFailedException::class);

        $connection = $this->createMock(ConnectionInterface::class);
        $client = new Client($connection);

        $reply = (new Reply($client))
            ->withLine(354, 'send data');

        $reply->assertCompleted();
    }

    /**
     * @test
     */
    public function it_an_error_400()
    {
        $connection = $this->createMock(ConnectionInterface::class);
        $client = new Client($connection);

        $reply = (new Reply($client))
            ->withLine(400, 'error');

        $this->assertTrue($reply->isError());
    }

    /**
     * @test
     */
    public function it_an_error_500()
    {
        $connection = $this->createMock(ConnectionInterface::class);
        $client = new Client($connection);

        $reply = (new Reply($client))
            ->withLine(500, 'error');

        $this->assertTrue($reply->isError());
    }

}