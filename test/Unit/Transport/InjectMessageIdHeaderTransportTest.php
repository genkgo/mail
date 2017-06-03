<?php

namespace Genkgo\TestMail\Unit\Transport;

use Genkgo\Mail\Header\MessageId;
use Genkgo\Mail\Transport\InjectMessageIdHeaderTransport;
use Genkgo\TestMail\AbstractTestCase;;
use Genkgo\Mail\GenericMessage;
use Genkgo\Mail\Transport\ArrayObjectTransport;

final class InjectMessageIdHeaderTransportTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_injects_message_id_header_in_message()
    {
        $message = new GenericMessage();
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

}