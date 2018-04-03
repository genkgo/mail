<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Quotation;

use Genkgo\Mail\Address;
use Genkgo\Mail\AlternativeText;
use Genkgo\Mail\GenericMessage;
use Genkgo\Mail\Header\Date;
use Genkgo\Mail\Header\From;
use Genkgo\Mail\MessageBodyCollection;
use Genkgo\Mail\Quotation\FixedQuotation;
use Genkgo\TestMail\AbstractTestCase;

final class FixedQuotationTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_quotes_other_message_body_collections()
    {
        $quotation = new FixedQuotation();

        $original = GenericMessage::fromString(
            \file_get_contents(__DIR__ . '/../../Stub/Quote/html-and-text.eml')
        );

        $reply = new MessageBodyCollection(
            '<html><head><title>Universe Title</title></head><body>Hello Universe</body>'
        );

        $this->assertSame(
            \file_get_contents(__DIR__ . '/../../Stub/Quote/html-and-text-quoted.html'),
            $quotation->quote($reply, $original)->getHtml()
        );
        $this->assertSame(
            \file_get_contents(__DIR__ . '/../../Stub/Quote/html-and-text-quoted.crlf.txt'),
            (string)$quotation->quote($reply, $original)->getText()
        );
    }

    /**
     * @test
     */
    public function it_quotes_already_quoted_other_message_body_collections()
    {
        $quotation = new FixedQuotation();

        $original = new MessageBodyCollection(
            \file_get_contents(__DIR__ . '/../../Stub/Quote/html-and-text-quoted.html')
        );

        $original = $original->withAlternativeText(
            new AlternativeText(
                \file_get_contents(__DIR__ . '/../../Stub/Quote/html-and-text-quoted.crlf.txt')
            )
        );

        $message = $original->createMessage()
            ->withHeader(new Date(new \DateTimeImmutable('2015-01-01 00:00:00')))
            ->withHeader(new From(Address::fromString('me <test@example.com>')));

        $reply = new MessageBodyCollection(
            '<html><head><title>Quoted Quoted</title></head><body><p>Lorem Ipsum<br />Lorem Ipsum</p></body>'
        );

        $this->assertSame(
            $this->replaceBoundaries(
                \file_get_contents(__DIR__ . '/../../Stub/Quote/double-quoted-html-and-text.eml')
            ),
            $this->replaceBoundaries(
                (string)$quotation->quote($reply, $message)->createMessage()
            )
        );
    }

    /**
     * @test
     */
    public function it_quotes_text_only()
    {
        $quotation = new FixedQuotation();

        $original = GenericMessage::fromString(
            \file_get_contents(__DIR__ . '/../../Stub/Quote/text.eml')
        );

        $reply = (new MessageBodyCollection(''))
            ->withAlternativeText(new AlternativeText('Hello Universe'));

        $this->assertSame(
            \file_get_contents(__DIR__ . '/../../Stub/Quote/text-quoted.crlf.txt'),
            (string)$quotation->quote($reply, $original)->getText()
        );
    }

    /**
     * @test
     */
    public function it_quotes_deep_text()
    {
        $quotation = new FixedQuotation();

        $original = GenericMessage::fromString(
            \file_get_contents(__DIR__ . '/../../Stub/Quote/deep-text.eml')
        );

        $reply = (new MessageBodyCollection(''))
            ->withAlternativeText(new AlternativeText('Hello Universe'));

        $this->assertSame(
            \file_get_contents(__DIR__ . '/../../Stub/Quote/deep-text-quoted.crlf.txt'),
            (string)$quotation->quote($reply, $original)->getText()
        );
    }
}
