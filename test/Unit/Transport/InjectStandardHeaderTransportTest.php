<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Transport;

use Genkgo\Mail\Address;
use Genkgo\Mail\AddressList;
use Genkgo\Mail\EmailAddress;
use Genkgo\Mail\Header\Date;
use Genkgo\Mail\Header\GenericHeader;
use Genkgo\Mail\Header\MessageId;
use Genkgo\Mail\Header\Sender;
use Genkgo\Mail\Transport\InjectStandardHeadersTransport;
use Genkgo\TestMail\AbstractTestCase;
use Genkgo\Mail\GenericMessage;
use Genkgo\Mail\Transport\ArrayObjectTransport;

final class InjectStandardHeaderTransportTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_injects_standard_headers_in_message()
    {
        $message = (new GenericMessage())
            ->withHeader(
                new GenericHeader(
                    'From',
                    (string) (new AddressList([
                        new Address(new EmailAddress('first@domain.com')),
                        new Address(new EmailAddress('second@domain.com')),
                    ]))
                )
            );

        $storage = new \ArrayObject();

        $transport = new InjectStandardHeadersTransport(
            new ArrayObjectTransport($storage),
            'domain'
        );

        $transport->send($message);

        $this->assertCount(1, $storage);
        $this->assertTrue($storage[0]->hasHeader('sender'));
        $this->assertTrue($storage[0]->hasHeader('date'));
        $this->assertTrue($storage[0]->hasHeader('message-id'));
        $this->assertInstanceOf(Sender::class, $storage[0]->getHeader('sender')[0]);
        $this->assertInstanceOf(Date::class, $storage[0]->getHeader('date')[0]);
        $this->assertInstanceOf(MessageId::class, $storage[0]->getHeader('message-id')[0]);
    }
}
