<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Protocol\Smtp\Backend;

use Genkgo\Mail\EmailAddress;
use Genkgo\Mail\PlainTextMessage;
use Genkgo\Mail\Protocol\Smtp\Backend\ArrayBackend;
use Genkgo\TestMail\AbstractTestCase;

final class ArrayBackendTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_contains_addresses()
    {
        $backend = new ArrayBackend(
            ['test@genkgo.nl'],
            new \ArrayObject()
        );

        $this->assertTrue($backend->contains(new EmailAddress('test@genkgo.nl')));
        $this->assertFalse($backend->contains(new EmailAddress('other@genkgo.nl')));
    }

    /**
     * @test
     */
    public function it_stores_messages()
    {
        $messages = new \ArrayObject();

        $backend = new ArrayBackend(
            ['test@genkgo.nl'],
            $messages
        );

        $message = new PlainTextMessage('test');

        $backend->store(
            new EmailAddress('test@genkgo.nl'),
            $message,
            'INBOX'
        );

        $this->assertTrue(isset($messages['test@genkgo.nl']['INBOX']));
        $this->assertCount(1, $messages['test@genkgo.nl']['INBOX']);
        $this->assertSame($message, $messages['test@genkgo.nl']['INBOX'][0]);
    }

    /**
     * @test
     */
    public function it_throws_when_storing_messages_to_unknown_user()
    {
        $this->expectException(\UnexpectedValueException::class);

        $backend = new ArrayBackend(
            ['test@genkgo.nl'],
            new \ArrayObject()
        );

        $backend->store(
            new EmailAddress('other@genkgo.nl'),
            new PlainTextMessage('test'),
            'INBOX'
        );
    }
}
