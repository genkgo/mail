<?php
declare(strict_types=1);

namespace Genkgo\Mail\Unit\Transport;

use Genkgo\Mail\AbstractTestCase;
use Genkgo\Mail\Address;
use Genkgo\Mail\AddressList;
use Genkgo\Mail\EmailAddress;
use Genkgo\Mail\GenericMessage;
use Genkgo\Mail\Header\From;
use Genkgo\Mail\Header\Subject;
use Genkgo\Mail\Header\To;
use Genkgo\Mail\Protocol\ConnectionInterface;
use Genkgo\Mail\Protocol\Smtp\Client;
use Genkgo\Mail\Protocol\Smtp\ProtocolOptions;
use Genkgo\Mail\Stream\BitEncodedStream;
use Genkgo\Mail\Transport\EnvelopeFactory;
use Genkgo\Mail\Transport\SmtpTransport;

final class SmtpTransportTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_sends_messages()
    {
        $at = -1;
        $mock = $this->createMock(ConnectionInterface::class);

        $mock
            ->expects($this->at(++$at))
            ->method('send')
            ->with("EHLO 127.0.0.1\r\n")
            ->willReturn(1);

        $mock
            ->expects($this->at(++$at))
            ->method('receive')
            ->willReturn("250 STARTTLS\r\n");

        $mock
            ->expects($this->at(++$at))
            ->method('send')
            ->with("MAIL FROM:<from@localhost>")
            ->willReturn(1);

        $mock
            ->expects($this->at(++$at))
            ->method('receive')
            ->willReturn("250 OK\r\n");

        $mock
            ->expects($this->at(++$at))
            ->method('send')
            ->with("RCPT TO:<to@localhost>")
            ->willReturn(1);

        $mock
            ->expects($this->at(++$at))
            ->method('receive')
            ->willReturn("250 OK\r\n");

        $mock
            ->expects($this->at(++$at))
            ->method('send')
            ->with("DATA\r\n")
            ->willReturn(1);

        $mock
            ->expects($this->at(++$at))
            ->method('receive')
            ->willReturn("354 Send message content; end with CRLF\r\n");

        $mock
            ->expects($this->at(++$at))
            ->method('send')
            ->with("Subject: subject\r\n")
            ->willReturn(1);

        $mock
            ->expects($this->at(++$at))
            ->method('send')
            ->with("From: name <from@localhost>\r\n")
            ->willReturn(1);

        $mock
            ->expects($this->at(++$at))
            ->method('send')
            ->with("To: name <to@localhost>\r\n")
            ->willReturn(1);

        $mock
            ->expects($this->at(++$at))
            ->method('send')
            ->with("MIME-Version: 1.0\r\n")
            ->willReturn(1);

        $mock
            ->expects($this->at(++$at))
            ->method('send')
            ->with("\r\n")
            ->willReturn(1);

        $mock
            ->expects($this->at(++$at))
            ->method('send')
            ->with("test\r\n")
            ->willReturn(1);

        $mock
            ->expects($this->at(++$at))
            ->method('send')
            ->with("test\r\n")
            ->willReturn(1);

        $mock
            ->expects($this->at(++$at))
            ->method('send')
            ->with(".\r\n")
            ->willReturn(1);

        $mock
            ->expects($this->at(++$at))
            ->method('receive')
            ->willReturn("250 OK\r\n");

        $transport = new SmtpTransport(
            new Client(
                $mock,
                new ProtocolOptions()
            ),
            EnvelopeFactory::useExtractedHeader()
        );

        $message = (new GenericMessage())
            ->withHeader(new From(new Address(new EmailAddress('from@localhost'), 'name')))
            ->withHeader(new To(new AddressList([new Address(new EmailAddress('to@localhost'), 'name')])))
            ->withHeader(new Subject('subject'))
            ->withBody(new BitEncodedStream("test\r\ntest"));

        $transport->send($message);
    }
}