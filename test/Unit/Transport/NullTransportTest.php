<?php

namespace Genkgo\Mail\Unit\Transport;

use Genkgo\Mail\AbstractTestCase;
use Genkgo\Mail\GenericMessage;
use Genkgo\Mail\Transport\NullTransport;

final class NullTransportTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_does_actually_nothing_at_all()
    {
        $message = new GenericMessage();

        $transport = new NullTransport();
        $transport->send($message);

        $this->assertTrue(true);
    }

}