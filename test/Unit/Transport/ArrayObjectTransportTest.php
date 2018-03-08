<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Transport;

use Genkgo\TestMail\AbstractTestCase;
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
