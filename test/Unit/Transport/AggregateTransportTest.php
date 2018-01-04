<?php

namespace Genkgo\TestMail\Unit\Transport;

use Genkgo\Mail\Transport\AggregateTransport;
use Genkgo\TestMail\AbstractTestCase;;
use Genkgo\Mail\GenericMessage;
use Genkgo\Mail\Transport\ArrayObjectTransport;

final class AggreateTransportTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_stores_message_in_an_array_object()
    {
        $message = new GenericMessage();
        $storage1 = new \ArrayObject();
        $storage2 = new \ArrayObject();

        $transport1 = new ArrayObjectTransport($storage1);
        $transport2 = new ArrayObjectTransport($storage2);

        $aggregate = new AggregateTransport([$transport1, $transport2]);
        $aggregate->send($message);

        $this->assertCount(1, $storage1);
        $this->assertCount(1, $storage2);
    }

}