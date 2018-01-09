<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Protocol\Smtp\Backend;

use Genkgo\Mail\EmailAddress;
use Genkgo\Mail\PlainTextMessage;
use Genkgo\Mail\Protocol\Smtp\Backend\ConsoleBackend;
use Genkgo\TestMail\AbstractTestCase;

final class ConsoleBackendTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_contains_addresses(): void
    {
        $backend = new ConsoleBackend();

        $this->assertTrue($backend->contains(new EmailAddress('test@genkgo.nl')));
        $this->assertTrue($backend->contains(new EmailAddress('other@genkgo.nl')));
    }

    /**
     * @test
     * @outputBuffering enabled
     */
    public function it_stores_messages(): void
    {
        $backend = new ConsoleBackend();

        $backend->store(
            new EmailAddress('test@genkgo.nl'),
            new PlainTextMessage('This is a test message'),
            'INBOX'
        );

        $this->expectOutputRegex('/MIME-Version: 1.0/');
        $this->expectOutputRegex('/Content-Type: text/plain; charset=us-ascii/');
        $this->expectOutputRegex('/Content-Transfer-Encoding: 7bit/');
        $this->expectOutputRegex('/This is a test message/');
    }
}
