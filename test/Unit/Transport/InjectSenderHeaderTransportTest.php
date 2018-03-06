<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Transport;

use Genkgo\Mail\Address;
use Genkgo\Mail\AddressList;
use Genkgo\Mail\EmailAddress;
use Genkgo\Mail\Header\From;
use Genkgo\Mail\Header\GenericHeader;
use Genkgo\Mail\Header\Sender;
use Genkgo\Mail\Transport\InjectSenderHeaderTransport;
use Genkgo\TestMail\AbstractTestCase;
use Genkgo\Mail\GenericMessage;
use Genkgo\Mail\Transport\ArrayObjectTransport;

final class InjectSenderHeaderTransportTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_injects_sender_header_in_message()
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

        $transport = new InjectSenderHeaderTransport(
            new ArrayObjectTransport($storage)
        );

        $transport->send($message);

        $this->assertCount(1, $storage);
        $this->assertTrue($storage[0]->hasHeader('sender'));
        $this->assertInstanceOf(Sender::class, $storage[0]->getHeader('sender')[0]);
    }

    /**
     * @test
     */
    public function it_leaves_message_without_from()
    {
        $message = (new GenericMessage());

        $storage = new \ArrayObject();

        $transport = new InjectSenderHeaderTransport(
            new ArrayObjectTransport($storage)
        );

        $transport->send($message);

        $this->assertCount(1, $storage);
        $this->assertFalse($storage[0]->hasHeader('sender'));
        $this->assertSame($storage[0], $message);
    }

    /**
     * @test
     */
    public function it_does_inject_when_only_one_address()
    {
        $message = (new GenericMessage())
            ->withHeader(new From(new Address(new EmailAddress('example@domain.com'))));

        $storage = new \ArrayObject();

        $transport = new InjectSenderHeaderTransport(
            new ArrayObjectTransport($storage)
        );

        $transport->send($message);

        $this->assertCount(1, $storage);
        $this->assertFalse($storage[0]->hasHeader('sender'));
        $this->assertSame($storage[0], $message);
    }
}
