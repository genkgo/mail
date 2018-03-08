<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Transport;

use Genkgo\TestMail\AbstractTestCase;
use Genkgo\Mail\GenericMessage;
use Genkgo\Mail\Header\Date;
use Genkgo\Mail\Transport\ArrayObjectTransport;
use Genkgo\Mail\Transport\InjectDateHeaderTransport;

final class InjectDateHeaderTransportTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_injects_date_header_in_message()
    {
        $message = new GenericMessage();
        $storage = new \ArrayObject();

        $transport = new InjectDateHeaderTransport(
            new ArrayObjectTransport($storage)
        );
        $transport->send($message);

        $this->assertCount(1, $storage);
        $this->assertTrue($storage[0]->hasHeader('date'));
        $this->assertInstanceOf(Date::class, $storage[0]->getHeader('date')[0]);
    }
}
