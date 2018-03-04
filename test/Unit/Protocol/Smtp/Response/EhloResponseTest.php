<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Protocol\Smtp\Response;

use Genkgo\Mail\Protocol\Smtp\Client;
use Genkgo\Mail\Protocol\Smtp\Reply;
use Genkgo\Mail\Protocol\Smtp\Response\EhloResponse;
use Genkgo\TestMail\AbstractTestCase;
use Genkgo\TestMail\Stub\FakeSmtpConnection;

final class EhloResponseTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_sends_advertisements()
    {
        $reply = (new Reply(new Client(new FakeSmtpConnection())))
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
        $reply = (new Reply(new Client(new FakeSmtpConnection())))
            ->withLine(250, 'hello')
            ->withLine(250, 'AUTH LOGIN PLAIN');

        $response = new EhloResponse($reply);
        $this->assertTrue($response->isAdvertising('AUTH LOGIN'));
        $this->assertTrue($response->isAdvertising('AUTH PLAIN'));
        $this->assertFalse($response->isAdvertising('XYZ'));
        $this->assertFalse($response->isAdvertising(''));
    }
}
