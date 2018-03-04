<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Protocol\Imap\Request;

use Genkgo\Mail\Protocol\Imap\Request\AuthPlainCredentialsRequest;
use Genkgo\Mail\Protocol\Imap\Tag;
use Genkgo\TestMail\AbstractTestCase;

final class AuthPlainCredentialsRequestTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_creates_a_stream()
    {
        $command = new AuthPlainCredentialsRequest(
            Tag::fromNonce(1),
            'username',
            'password'
        );

        $this->assertSame(
            \base64_encode("\0username\0password"),
            (string)$command->toStream()
        );
    }
}
