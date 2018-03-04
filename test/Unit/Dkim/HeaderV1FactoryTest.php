<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Dkim;

use Genkgo\Mail\Address;
use Genkgo\Mail\AddressList;
use Genkgo\Mail\Dkim\CanonicalizeBodyInterface;
use Genkgo\Mail\Dkim\CanonicalizeHeaderInterface;
use Genkgo\Mail\Dkim\HeaderV1Factory;
use Genkgo\Mail\Dkim\Parameters;
use Genkgo\Mail\Dkim\SignInterface;
use Genkgo\Mail\EmailAddress;
use Genkgo\Mail\Header\Bcc;
use Genkgo\Mail\Header\Date;
use Genkgo\Mail\Header\From;
use Genkgo\Mail\Header\MessageId;
use Genkgo\Mail\Header\To;
use Genkgo\Mail\PlainTextMessage;
use Genkgo\TestMail\AbstractTestCase;

final class HeaderV1FactoryTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_creates_a_dkim_signature()
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

        $factory = new HeaderV1Factory($signer, $headerCanonicalize, $bodyCanonicalize);
        $header = $factory->factory($message, new Parameters('x', 'example.com'));

        $this->assertEquals('DKIM-Signature', (string)$header->getName());
        $this->assertEquals(
            \file_get_contents(__DIR__.'/../../Stub/Dkim/dkim_factory_test_header.eml'),
            (string)$header->getValue()
        );
    }

    /**
     * @test
     */
    public function it_skips_bcc_header_signing_signature()
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
            ->withHeader(new Bcc(new AddressList([new Address(new EmailAddress('bcc@genkgodev.com'))])))
            ->withHeader(new MessageId('testing', 'genkgodev.com'));

        $factory = new HeaderV1Factory($signer, $headerCanonicalize, $bodyCanonicalize);
        $header = $factory->factory($message, new Parameters('x', 'example.com'));

        $this->assertEquals(
            \file_get_contents(__DIR__.'/../../Stub/Dkim/dkim_factory_test_header.eml'),
            (string)$header->getValue()
        );
    }
}
