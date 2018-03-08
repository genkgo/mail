<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Transport;

use Genkgo\TestMail\AbstractTestCase;
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
