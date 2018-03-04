<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Transport;

use Genkgo\Mail\Exception\EnvelopeException;
use Genkgo\TestMail\AbstractTestCase;
use Genkgo\Mail\Address;
use Genkgo\Mail\AddressList;
use Genkgo\Mail\EmailAddress;
use Genkgo\Mail\GenericMessage;
use Genkgo\Mail\Header\From;
use Genkgo\Mail\Header\Subject;
use Genkgo\Mail\Header\To;
use Genkgo\Mail\Stream\AsciiEncodedStream;
use Genkgo\Mail\Transport\EnvelopeFactory;
use Genkgo\Mail\Transport\PhpMailTransport;

final class PhpMailTransportTest extends AbstractTestCase
{
    /**
     * @var array
     */
    private $expects = [];

    /**
     * @test
     */
    public function it_sends_messages()
    {
        $transport = PhpMailTransport::newReplaceMailMethod(
            \Closure::fromCallable([$this, 'callbackTestMailParameters']),
            EnvelopeFactory::useExtractedHeader(),
            ['-ODeliveryMode=d']
        );

        $this->expects = [
            'name <to@localhost>',
            'subject',
            'test',
            "From: name <from@localhost>\r\nMIME-Version: 1.0",
            '-ODeliveryMode=d -ffrom@localhost'
        ];

        $message = (new GenericMessage())
            ->withHeader(new From(new Address(new EmailAddress('from@localhost'), 'name')))
            ->withHeader(new To(new AddressList([new Address(new EmailAddress('to@localhost'), 'name')])))
            ->withHeader(new Subject('subject'))
            ->withBody(new AsciiEncodedStream('test'));

        $transport->send($message);
    }

    /**
     * @test
     */
    public function it_throws_when_missing_to_header()
    {
        $this->expectException(\InvalidArgumentException::class);

        $transport = PhpMailTransport::newReplaceMailMethod(
            \Closure::fromCallable([$this, 'callbackTestMailParameters']),
            EnvelopeFactory::useExtractedHeader(),
            ['-ODeliveryMode=d']
        );

        $message = (new GenericMessage())
            ->withHeader(new Subject('subject'))
            ->withBody(new AsciiEncodedStream('test'));

        $transport->send($message);
    }

    /**
     * @test
     */
    public function it_throws_when_missing_subject_header()
    {
        $this->expectException(\InvalidArgumentException::class);

        $transport = PhpMailTransport::newReplaceMailMethod(
            \Closure::fromCallable([$this, 'callbackTestMailParameters']),
            EnvelopeFactory::useExtractedHeader(),
            ['-ODeliveryMode=d']
        );

        $message = (new GenericMessage())
            ->withHeader(new To(new AddressList([new Address(new EmailAddress('to@localhost'), 'name')])))
            ->withBody(new AsciiEncodedStream('test'));

        $transport->send($message);
    }

    /**
     * @test
     */
    public function it_prevents_parameter_injection()
    {
        $this->expectException(EnvelopeException::class);
        $this->expectExceptionMessage('Unable to guarantee injection-free envelop');

        $transport = PhpMailTransport::newReplaceMailMethod(
            \Closure::fromCallable([$this, 'callbackTestMailParameters']),
            EnvelopeFactory::useExtractedHeader()
        );

        $injection = '\'a."\'\ -OQueueDirectory=\%0D<?=eval($_GET[c])?>\ -X/var/www/html/"@a.php';

        $message = (new GenericMessage())
            ->withHeader(new From(new Address(new EmailAddress($injection), 'name')))
            ->withHeader(new To(new AddressList([new Address(new EmailAddress('to@localhost'), 'name')])))
            ->withHeader(new Subject('subject'))
            ->withBody(new AsciiEncodedStream('test'));

        $transport->send($message);
    }

    /**
     * @param array ...$arguments
     */
    private function callbackTestMailParameters(...$arguments)
    {
        $this->assertEquals($this->expects, $arguments);
    }
}
