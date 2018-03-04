<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Transport;

use Genkgo\Mail\Address;
use Genkgo\Mail\AddressList;
use Genkgo\Mail\Dkim\CanonicalizeBodyInterface;
use Genkgo\Mail\Dkim\CanonicalizeHeaderInterface;
use Genkgo\Mail\Dkim\HeaderV1Factory;
use Genkgo\Mail\Dkim\Parameters;
use Genkgo\Mail\Dkim\SignInterface;
use Genkgo\Mail\EmailAddress;
use Genkgo\Mail\Header\Date;
use Genkgo\Mail\Header\From;
use Genkgo\Mail\Header\MessageId;
use Genkgo\Mail\Header\To;
use Genkgo\Mail\MessageInterface;
use Genkgo\Mail\PlainTextMessage;
use Genkgo\Mail\Transport\DkimV1SignedTransport;
use Genkgo\Mail\TransportInterface;
use Genkgo\TestMail\AbstractTestCase;

final class DkimV1SignedTransportTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_adds_a_dkim_header_to_message()
    {
        $signer = $this->createMock(SignInterface::class);
        $signer->expects($this->at(0))
            ->method('hashBody')
            ->willReturn('hash');

        $signer->expects($this->at(1))
            ->method('name')
            ->willReturn('rsa-sha256');

        $signer->expects($this->at(2))
            ->method('signHeaders')
            ->willReturn('signature');

        $headerCanonicalize = $this->createMock(CanonicalizeHeaderInterface::class);
        $headerCanonicalize
            ->expects($this->at(0))
            ->method('name')
            ->willReturn('relaxed');

        $bodyCanonicalize = $this->createMock(CanonicalizeBodyInterface::class);
        $bodyCanonicalize
            ->expects($this->at(0))
            ->method('name')
            ->willReturn('relaxed');

        $message = (new PlainTextMessage('Hello World'))
            ->withHeader(new Date(new \DateTimeImmutable('1/1/2017')))
            ->withHeader(new From(new Address(new EmailAddress('sender@genkgodev.com'))))
            ->withHeader(new To(new AddressList([new Address(new EmailAddress('recipient@genkgodev.com'))])))
            ->withHeader(new MessageId('testing', 'genkgodev.com'));

        $decoratedTransport = $this->createMock(TransportInterface::class);

        $decoratedTransport
            ->expects($this->at(0))
            ->method('send')
            ->with(
                $this->callback(
                    function (MessageInterface $message) {
                        $this->assertTrue($message->hasHeader('dkim-signature'));
                        $headers = $message->getHeader('dkim-signature');
                        $this->assertCount(1, $headers);

                        $header = \reset($headers);
                        $this->assertGreaterThan(0, $header->getValue()->getParameter('t')->getValue());
                        return true;
                    }
                )
            );

        $transport = new DkimV1SignedTransport(
            $decoratedTransport,
            new HeaderV1Factory($signer, $headerCanonicalize, $bodyCanonicalize),
            new Parameters('x', 'genkgodev.com')
        );

        $transport->send($message);
    }
}
