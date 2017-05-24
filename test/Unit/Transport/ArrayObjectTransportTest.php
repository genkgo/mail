<?php

namespace Genkgo\Mail\Unit\Transport;

use Genkgo\Mail\AbstractTestCase;
use Genkgo\Mail\GenericMessage;
use Genkgo\Mail\Transport\ArrayObjectTransport;

final class ArrayObjectTransportTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_stores_message_in_an_array_object()
    {
        $message = new GenericMessage();
        $storage = new \ArrayObject();

        $transport = new ArrayObjectTransport($storage);
        $transport->send($message);

        $this->assertCount(1, $storage);
    }

}