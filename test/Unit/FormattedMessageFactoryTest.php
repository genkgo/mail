<?php

namespace Genkgo\TestMail\Unit;

use Genkgo\Mail\AlternativeText;
use Genkgo\TestMail\AbstractTestCase;;
use Genkgo\Mail\Address;
use Genkgo\Mail\AddressList;
use Genkgo\Mail\EmailAddress;
use Genkgo\Mail\FormattedMessageFactory;
use Genkgo\Mail\Header\Cc;
use Genkgo\Mail\Header\ContentID;
use Genkgo\Mail\Header\ContentType;
use Genkgo\Mail\Header\Subject;
use Genkgo\Mail\Header\To;
use Genkgo\Mail\Mime\EmbeddedImage;
use Genkgo\Mail\Mime\HtmlPart;
use Genkgo\Mail\Mime\ResourceAttachment;
use Genkgo\Mail\Stream\AsciiEncodedStream;

/**
 * Class FormattedMessageFactoryTest
 * @package Genkgo\Mail\Unit
 */
final class FormattedMessageFactoryTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_is_immutable()
    {
        $message = new FormattedMessageFactory();

        $this->assertNotSame(
            $message,
            $message->withEmbeddedImage(
                new EmbeddedImage(
                    new AsciiEncodedStream(
                        base64_decode('R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==')
                    ),
                    'pixel.gif',
                    new ContentType('image/gif'),
                    new ContentID('123456')
                )
            )
        );

        $this->assertNotSame(
            $message,
            $message->withAttachment(
                ResourceAttachment::fromString(
                    'Attachment text',
                    'attachment.txt',
                    new ContentType('plain/text')
                )
            )
        );

        $this->assertNotSame(
            $message,
            $message->withHtml('<html></html>')
        );

        $this->assertNotSame(
            $message,
            $message->withHtmlAndNoGeneratedAlternativeText('<html></html>')
        );

        $this->assertNotSame(
            $message,
            $message->withAlternativeText(new AlternativeText('text'))
        );
    }

    /**
     * @test
     */
    public function it_throws_exception_when_attachment_does_not_have_content_disposition()
    {
        $this->expectException(\InvalidArgumentException::class);

        $message = new FormattedMessageFactory();

        $message->withAttachment(
            new HtmlPart('<html></html>')
        );
    }

    /**
     * @test
     */
    public function it_throws_exception_when_attachment_header_content_disposition_does_not_equals_attachment()
    {
        $this->expectException(\InvalidArgumentException::class);

        $message = new FormattedMessageFactory();

        $message->withAttachment(
            new EmbeddedImage(
                new AsciiEncodedStream(
                    base64_decode('R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==')
                ),
                'pixel.gif',
                new ContentType('image/gif'),
                new ContentID('123456')
            )
        );
    }

    /**
     * @test
     */
    public function it_should_equal_file_when_html_only ()
    {
        $factory = (new FormattedMessageFactory())
            ->withHtmlAndNoGeneratedAlternativeText('<html><body><p>Hello World</p></body></html>');

        $message = $factory->createMessage()
            ->withHeader(new Subject('Hello World'))
            ->withHeader((new To(new AddressList([new Address(new EmailAddress('me@example.com'), 'me')]))))
            ->withHeader((new Cc(new AddressList([new Address(new EmailAddress('other@example.com'), 'other')]))));

        $this->assertEquals(
            file_get_contents(__DIR__ . '/../Stub/FormattedMessageFactoryTest/html-only.eml'),
            (string) $message
        );
    }

    /**
     * @test
     */
    public function it_should_equal_empty_text_plain_message_when_no_and_no_text ()
    {
        $factory = (new FormattedMessageFactory());

        $message = $factory->createMessage()
            ->withHeader(new Subject('Hello World'))
            ->withHeader((new To(new AddressList([new Address(new EmailAddress('me@example.com'), 'me')]))))
            ->withHeader((new Cc(new AddressList([new Address(new EmailAddress('other@example.com'), 'other')]))));

        $this->assertEquals(
            file_get_contents(__DIR__ . '/../Stub/FormattedMessageFactoryTest/empty-email.eml'),
            (string) $message
        );
    }

    /**
     * @test
     */
    public function it_should_equal_file_expect_boundaries_when_full_formatted_message ()
    {
        $message = (new FormattedMessageFactory())
            ->withHtml('<html><body><p>Hello World</p></body></html>')
            ->withAttachment(
                ResourceAttachment::fromString(
                    'Attachment text',
                    'attachment.txt',
                    new ContentType('text/plain')
                )
            )
            ->withEmbeddedImage(
                new EmbeddedImage(
                    new AsciiEncodedStream(
                        base64_decode('R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==')
                    ),
                    'pixel.gif',
                    new ContentType('image/gif'),
                    new ContentID('123456')
                )
            )
            ->createMessage()
            ->withHeader(new Subject('Hello World'))
            ->withHeader((new To(new AddressList([new Address(new EmailAddress('me@example.com'), 'me')]))))
            ->withHeader((new Cc(new AddressList([new Address(new EmailAddress('other@example.com'), 'other')]))));

        $this->assertEquals(
            $this->replaceBoundaries(
                file_get_contents(__DIR__ . '/../Stub/FormattedMessageFactoryTest/full-formatted-message.eml'),
                'boundary'
            ),
            $this->replaceBoundaries(
                (string) $message,
                'boundary'
            )
        );
    }

    /**
     * @param $messageString
     * @param $boundary
     * @return string
     */
    private function replaceBoundaries(string $messageString, string $boundary): string
    {
        return preg_replace(['/(GenkgoMailV2Part[A-Za-z0-9\-]*)/'], $boundary, $messageString);
    }
}