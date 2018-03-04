<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Protocol\Smtp\Backend;

use Genkgo\Mail\EmailAddress;
use Genkgo\Mail\PlainTextMessage;
use Genkgo\Mail\Protocol\Smtp\Backend\UnknownUserBackend;
use Genkgo\TestMail\AbstractTestCase;

final class UnknownUserBackendTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_contains_addresses()
    {
        $backend = new UnknownUserBackend();

        $this->assertFalse($backend->contains(new EmailAddress('test@genkgo.nl')));
        $this->assertFalse($backend->contains(new EmailAddress('other@genkgo.nl')));
    }

    /**
     * @test
     */
    public function it_throws_when_storing_messages()
    {
        $this->expectException(\UnexpectedValueException::class);

        $backend = new UnknownUserBackend();

        $backend->store(
            new EmailAddress('test@genkgo.nl'),
            new PlainTextMessage('test'),
            'INBOX'
        );
    }
}
