<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Protocol\Smtp;

use Genkgo\Mail\Exception\AssertionFailedException;
use Genkgo\Mail\Protocol\Smtp\Client;
use Genkgo\Mail\Protocol\Smtp\Reply;
use Genkgo\TestMail\AbstractTestCase;
use Genkgo\TestMail\Stub\FakeSmtpConnection;

final class ReplyTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_has_messages(): void
    {
        $reply = (new Reply(new Client(new FakeSmtpConnection())))
            ->withLine(250, 'hello')
            ->withLine(250, 'STARTTLS');

        $this->assertFalse($reply->isError());
        $this->assertEquals(['hello', 'STARTTLS'], $reply->getMessages());
    }

    /**
     * @test
     */
    public function it_asserts(): void
    {
        $client = new Client(new FakeSmtpConnection());

        $reply = (new Reply($client))
            ->withLine(250, 'hello')
            ->withLine(250, 'STARTTLS');

        $this->assertFalse($reply->isError());
        $this->assertEquals($client, $reply->assert(250));
    }

    /**
     * @test
     */
    public function it_asserts_completed(): void
    {
        $client = new Client(new FakeSmtpConnection());

        $reply = (new Reply($client))
            ->withLine(250, 'hello')
            ->withLine(250, 'STARTTLS');

        $this->assertFalse($reply->isError());
        $this->assertEquals($client, $reply->assertCompleted());
    }

    /**
     * @test
     */
    public function it_asserts_intermediate(): void
    {
        $client = new Client(new FakeSmtpConnection());

        $reply = (new Reply($client))
            ->withLine(354, 'send data');

        $this->assertFalse($reply->isError());
        $this->assertEquals($client, $reply->assertIntermediate());
    }

    /**
     * @test
     */
    public function it_throws_when_making_wrong_assertions(): void
    {
        $this->expectException(AssertionFailedException::class);

        $client = new Client(new FakeSmtpConnection());

        $reply = (new Reply($client))
            ->withLine(354, 'send data');

        $reply->assertCompleted();
    }

    /**
     * @test
     */
    public function it_is_an_error_400(): void
    {
        $client = new Client(new FakeSmtpConnection());

        $reply = (new Reply($client))
            ->withLine(400, 'error');

        $this->assertTrue($reply->isError());
    }

    /**
     * @test
     */
    public function it_is_an_error_500(): void
    {
        $client = new Client(new FakeSmtpConnection());

        $reply = (new Reply($client))
            ->withLine(500, 'error');

        $this->assertTrue($reply->isError());
    }

    /**
     * @test
     */
    public function it_is_an_implemented_command(): void
    {
        $client = new Client(new FakeSmtpConnection());

        $reply = (new Reply($client))
            ->withLine(250, 'Ok');

        $this->assertFalse($reply->isCommandNotImplemented());
    }

    /**
     * @test
     */
    public function it_is_an_unimplemented_command(): void
    {
        $client = new Client(new FakeSmtpConnection());

        $reply = (new Reply($client))
            ->withLine(502, 'error');

        $this->assertTrue($reply->isCommandNotImplemented());
    }
}
