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
    public function it_sends_messages(): void
    {
        $connection = $this->createMock(ConnectionInterface::class);

        $message = (new GenericMessage())
            ->withHeader(new From(new Address(new EmailAddress('from@localhost'), 'name')))
            ->withHeader(new To(new AddressList([new Address(new EmailAddress('to@localhost'), 'name')])))
            ->withHeader(new Subject('subject'))
            ->withBody(new AsciiEncodedStream("test\r\ntest"));

        $connection
            ->expects($this->exactly(1))
            ->method('addListener');

        $connection
            ->expects($this->exactly(8))
            ->method('send')
            ->withConsecutive(
                ["TAG1 APPEND INBOX {103}\r\n"],
                ["Subject: subject\r\n"],
                ["From: name <from@localhost>\r\n"],
                ["To: name <to@localhost>\r\n"],
                ["MIME-Version: 1.0\r\n"],
                ["\r\n"],
                ["test\r\n"],
                ["test\r\n"]
            )
            ->willReturn(1);

        $connection
            ->expects($this->exactly(2))
            ->method('receive')
            ->willReturnOnConsecutiveCalls(
                "+ Send message\r\n",
                "TAG1 OK\r\n"
            );

        $transport = new ImapTransport(
            new Client($connection, new GeneratorTagFactory()),
            new MailboxName('INBOX')
        );

        $transport->send($message);
    }
}
