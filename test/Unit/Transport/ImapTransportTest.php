<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Transport;

use Genkgo\Mail\Protocol\Imap\Client;
use Genkgo\Mail\Protocol\Imap\MailboxName;
use Genkgo\Mail\Protocol\Imap\TagFactory\GeneratorTagFactory;
use Genkgo\Mail\Transport\ImapTransport;
use Genkgo\TestMail\AbstractTestCase;
use Genkgo\Mail\Address;
use Genkgo\Mail\AddressList;
use Genkgo\Mail\EmailAddress;
use Genkgo\Mail\GenericMessage;
use Genkgo\Mail\Header\From;
use Genkgo\Mail\Header\Subject;
use Genkgo\Mail\Header\To;
use Genkgo\Mail\Protocol\ConnectionInterface;
use Genkgo\Mail\Stream\AsciiEncodedStream;

final class ImapTransportTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_sends_messages()
    {
        $at = -1;
        $connection = $this->createMock(ConnectionInterface::class);

        $message = (new GenericMessage())
            ->withHeader(new From(new Address(new EmailAddress('from@localhost'), 'name')))
            ->withHeader(new To(new AddressList([new Address(new EmailAddress('to@localhost'), 'name')])))
            ->withHeader(new Subject('subject'))
            ->withBody(new AsciiEncodedStream("test\r\ntest"));

        $connection
            ->expects($this->at(++$at))
            ->method('addListener');

        $connection
            ->expects($this->at(++$at))
            ->method('send')
            ->with("TAG1 APPEND INBOX {103}\r\n")
            ->willReturn(1);

        $connection
            ->expects($this->at(++$at))
            ->method('receive')
            ->willReturn("+ Send message\r\n");

        $connection
            ->expects($this->at(++$at))
            ->method('send')
            ->with("Subject: subject\r\n")
            ->willReturn(1);

        $connection
            ->expects($this->at(++$at))
            ->method('send')
            ->with("From: name <from@localhost>\r\n")
            ->willReturn(1);

        $connection
            ->expects($this->at(++$at))
            ->method('send')
            ->with("To: name <to@localhost>\r\n")
            ->willReturn(1);

        $connection
            ->expects($this->at(++$at))
            ->method('send')
            ->with("MIME-Version: 1.0\r\n")
            ->willReturn(1);

        $connection
            ->expects($this->at(++$at))
            ->method('send')
            ->with("\r\n")
            ->willReturn(1);

        $connection
            ->expects($this->at(++$at))
            ->method('send')
            ->with("test\r\n")
            ->willReturn(1);

        $connection
            ->expects($this->at(++$at))
            ->method('send')
            ->with("test\r\n")
            ->willReturn(1);

        $connection
            ->expects($this->at(++$at))
            ->method('receive')
            ->willReturn("TAG1 OK\r\n");

        $transport = new ImapTransport(
            new Client($connection, new GeneratorTagFactory()),
            new MailboxName('INBOX')
        );

        $transport->send($message);
    }
}
