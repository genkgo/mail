<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Transport;

use Genkgo\TestMail\AbstractTestCase;
use Genkgo\Mail\Address;
use Genkgo\Mail\AddressList;
use Genkgo\Mail\EmailAddress;
use Genkgo\Mail\GenericMessage;
use Genkgo\Mail\Header\From;
use Genkgo\Mail\Header\HeaderLine;
use Genkgo\Mail\Header\Subject;
use Genkgo\Mail\Header\To;
use Genkgo\Mail\HeaderInterface;
use Genkgo\Mail\Protocol\ConnectionInterface;
use Genkgo\Mail\Protocol\Smtp\Client;
use Genkgo\Mail\Stream\AsciiEncodedStream;
use Genkgo\Mail\Transport\EnvelopeFactory;
use Genkgo\Mail\Transport\SmtpTransport;

final class SmtpTransportTest extends AbstractTestCase
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

        $sendCalls = [
            ["MAIL FROM:<from@localhost>\r\n"],
            ["RCPT TO:<to@localhost>\r\n"],
            ["DATA\r\n"]
        ];

        foreach ($message->getHeaders() as $headers) {
            /** @var HeaderInterface $header */
            foreach ($headers as $header) {
                $sendCalls[] = [\sprintf("%s\r\n", (string)(new HeaderLine($header)))];
            }
        }

        $sendCalls[] = ["\r\n"];

        foreach (\explode("\r\n", (string)$message->getBody()) as $line) {
            $sendCalls[] = [\sprintf("%s\r\n", $line)];
        }

        $sendCalls[] = [".\r\n"];

        $connection
            ->expects($this->any())
            ->method('send')
            ->withConsecutive(...$sendCalls)
            ->willReturn(1);

        $connection
            ->expects($this->any())
            ->method('receive')
            ->willReturnOnConsecutiveCalls(
                "250 OK\r\n",
                "250 OK\r\n",
                "354 Send message content; end with CRLF\r\n",
                "250 OK\r\n"
            );

        $transport = new SmtpTransport(
            new Client($connection),
            EnvelopeFactory::useExtractedHeader()
        );

        $transport->send($message);
    }
}
