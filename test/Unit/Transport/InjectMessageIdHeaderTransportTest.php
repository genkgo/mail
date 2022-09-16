<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Transport;

use Genkgo\Mail\Header\MessageId;
use Genkgo\Mail\HeaderInterface;
use Genkgo\Mail\MessageInterface;
use Genkgo\Mail\Transport\InjectMessageIdHeaderTransport;
use Genkgo\TestMail\AbstractTestCase;
use Genkgo\Mail\GenericMessage;
use Genkgo\Mail\Transport\ArrayObjectTransport;

final class InjectMessageIdHeaderTransportTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_injects_message_id_header_in_message(): void
    {
        $message = new GenericMessage();
        /** @var \ArrayObject<int, MessageInterface> $storage */
        $storage = new \ArrayObject();

        $transport = new InjectMessageIdHeaderTransport(
            new ArrayObjectTransport($storage),
            'domain'
        );
        $transport->send($message);

        $this->assertCount(1, $storage);
        $this->assertTrue($storage[0]->hasHeader('message-id'));
        $this->assertInstanceOf(MessageId::class, $storage[0]->getHeader('message-id')[0]);
    }

    /**
     * @test
     */
    public function it_does_not_inject_a_message_id_header_when_there_is_one(): void
    {
        $message = (new GenericMessage())
            ->withHeader(new MessageId('left', 'right'));

        /** @var \ArrayObject<int, MessageInterface> $storage */
        $storage = new \ArrayObject();

        $transport = new InjectMessageIdHeaderTransport(
            new ArrayObjectTransport($storage),
            'domain'
        );
        $transport->send($message);

        $this->assertCount(1, $storage);
        $this->assertTrue($storage[0]->hasHeader('message-id'));
        $this->assertInstanceOf(MessageId::class, $storage[0]->getHeader('message-id')[0]);

        /** @var HeaderInterface $header */
        $header = $storage[0]->getHeader('message-id')[0];
        $this->assertEquals('<left@right>', (string)$header->getValue());
    }
}
