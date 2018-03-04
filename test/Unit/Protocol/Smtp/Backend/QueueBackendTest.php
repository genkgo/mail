<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Protocol\Smtp\Backend;

use Genkgo\Mail\EmailAddress;
use Genkgo\Mail\PlainTextMessage;
use Genkgo\Mail\Protocol\Smtp\Backend\QueueBackend;
use Genkgo\Mail\Queue\ArrayObjectQueue;
use Genkgo\TestMail\AbstractTestCase;

final class QueueBackendTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_contains_addresses()
    {
        $backend = new QueueBackend(new ArrayObjectQueue(new \ArrayObject()));

        $this->assertTrue($backend->contains(new EmailAddress('test@genkgo.nl')));
        $this->assertTrue($backend->contains(new EmailAddress('other@genkgo.nl')));
    }

    /**
     * @test
     */
    public function it_stores_messages()
    {
        $messages = new \ArrayObject();
        $backend = new QueueBackend(new ArrayObjectQueue($messages));

        $backend->store(
            new EmailAddress('test@genkgo.nl'),
            new PlainTextMessage('test'),
            'INBOX'
        );

        $this->assertCount(1, $messages);
        $this->assertSame((string)new PlainTextMessage('test'), $messages[0]);
    }
}
