<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Protocol\Imap\Request;

use Genkgo\Mail\Protocol\Imap\Request\LoginCommand;
use Genkgo\Mail\Protocol\Imap\Tag;
use Genkgo\TestMail\AbstractTestCase;

final class LoginCommandTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_creates_a_stream()
    {
        $command = new LoginCommand(
            Tag::fromNonce(1),
            'username',
            'password'
        );

        $this->assertSame('TAG1 LOGIN username password', (string)$command->toStream());
        $this->assertSame('TAG1', (string)$command->getTag());
    }

    /**
     * @test
     */
    public function it_throws_when_username_contains_whitespace()
    {
        $this->expectException(\InvalidArgumentException::class);

        new LoginCommand(
            Tag::fromNonce(1),
            'username ',
            'password'
        );
    }

    /**
     * @test
     */
    public function it_throws_when_password_contains_whitespace()
    {
        $this->expectException(\InvalidArgumentException::class);

        new LoginCommand(
            Tag::fromNonce(1),
            'username',
            'password '
        );
    }
}
