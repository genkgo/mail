<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit;

use Genkgo\Mail\Address;
use Genkgo\Mail\AddressList;
use Genkgo\Mail\AlternativeText;
use Genkgo\Mail\EmailAddress;
use Genkgo\Mail\GenericMessage;
use Genkgo\Mail\Header\Cc;
use Genkgo\Mail\Header\ContentID;
use Genkgo\Mail\Header\ContentType;
use Genkgo\Mail\Header\Subject;
use Genkgo\Mail\Header\To;
use Genkgo\Mail\MessageBodyCollection;
use Genkgo\Mail\Mime\EmbeddedImage;
use Genkgo\Mail\Mime\HtmlPart;
use Genkgo\Mail\Mime\ResourceAttachment;
use Genkgo\Mail\Stream\AsciiEncodedStream;
use Genkgo\TestMail\AbstractTestCase;

final class MessageBodyCollectionTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_is_immutable()
    {
        $message = new MessageBodyCollection();

        $this->assertNotSame(
            $message,
            $message->withEmbeddedImage(
                new EmbeddedImage(
                    new AsciiEncodedStream(
                        \base64_decode('R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==')
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

        $message = new MessageBodyCollection();

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

        $message = new MessageBodyCollection();

        $message->withAttachment(
            new EmbeddedImage(
                new AsciiEncodedStream(
                    \base64_decode('R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==')
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
    public function it_should_equal_file_when_html_only()
    {
        $body = (new MessageBodyCollection())
            ->withHtmlAndNoGeneratedAlternativeText('<html><body><p>Hello World</p></body></html>');

        $message = $body->createMessage()
            ->withHeader(new Subject('Hello World'))
            ->withHeader((new To(new AddressList([new Address(new EmailAddress('me@example.com'), 'me')]))))
            ->withHeader((new Cc(new AddressList([new Address(new EmailAddress('other@example.com'), 'other')]))));

        $this->assertEquals(
            \file_get_contents(__DIR__ . '/../Stub/MessageBodyCollectionTest/html-only.eml'),
            (string) $message
        );
    }

    /**
     * @test
     */
    public function it_should_equal_file_when_attaching_to_existing_html_only_message()
    {
        $body = (new MessageBodyCollection())
            ->withHtmlAndNoGeneratedAlternativeText('<html><body><p>Hello World</p></body></html>');

        $message = (new GenericMessage())
            ->withHeader(new Subject('Hello World'))
            ->withHeader((new To(new AddressList([new Address(new EmailAddress('me@example.com'), 'me')]))))
            ->withHeader((new Cc(new AddressList([new Address(new EmailAddress('other@example.com'), 'other')]))));

        $message = $body->attachToMessage($message);

        $this->assertEquals(
            \file_get_contents(__DIR__ . '/../Stub/MessageBodyCollectionTest/html-only.eml'),
            (string) $message
        );
    }

    /**
     * @test
     */
    public function it_should_equal_file_when_text_only()
    {
        $body = (new MessageBodyCollection())
            ->withAlternativeText(new AlternativeText('Hello World'));

        $message = $body->createMessage()
            ->withHeader(new Subject('Hello World'))
            ->withHeader((new To(new AddressList([new Address(new EmailAddress('me@example.com'), 'me')]))))
            ->withHeader((new Cc(new AddressList([new Address(new EmailAddress('other@example.com'), 'other')]))));

        $this->assertEquals(
            \file_get_contents(__DIR__ . '/../Stub/MessageBodyCollectionTest/text-only.eml'),
            (string) $message
        );
    }

    /**
     * @test
     */
    public function it_should_equal_empty_text_plain_message_when_no_and_no_text()
    {
        $body = (new MessageBodyCollection());

        $message = $body->createMessage()
            ->withHeader(new Subject('Hello World'))
            ->withHeader((new To(new AddressList([new Address(new EmailAddress('me@example.com'), 'me')]))))
            ->withHeader((new Cc(new AddressList([new Address(new EmailAddress('other@example.com'), 'other')]))));

        $this->assertEquals(
            \file_get_contents(__DIR__ . '/../Stub/MessageBodyCollectionTest/empty-email.eml'),
            (string) $message
        );
    }

    /**
     * @test
     */
    public function it_should_equal_file_expect_boundaries_when_full_formatted_message()
    {
        $message = (new MessageBodyCollection())
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
                        \base64_decode('R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==')
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
                \file_get_contents(__DIR__ . '/../Stub/MessageBodyCollectionTest/full-formatted-message.eml'),
                'boundary'
            ),
            $this->replaceBoundaries(
                (string) $message,
                'boundary'
            )
        );
    }

    /**
     * @test
     */
    public function it_should_equal_file_expect_boundaries_when_html_and_text_formatted_message()
    {
        $message = (new MessageBodyCollection())
            ->withHtml('<html><body><p>Hello World</p></body></html>')
            ->createMessage()
            ->withHeader(new Subject('Hello World'))
            ->withHeader((new To(new AddressList([new Address(new EmailAddress('me@example.com'), 'me')]))))
            ->withHeader((new Cc(new AddressList([new Address(new EmailAddress('other@example.com'), 'other')]))));

        $this->assertEquals(
            $this->replaceBoundaries(
                \file_get_contents(__DIR__ . '/../Stub/MessageBodyCollectionTest/html-and-text.eml'),
                'boundary'
            ),
            $this->replaceBoundaries(
                (string) $message,
                'boundary'
            )
        );
    }
    
    /**
     * @test
     */
    public function it_extracts_body_from_full_formatted_messages()
    {
        $message = GenericMessage::fromString(
            \file_get_contents(__DIR__ . '/../Stub/MessageBodyCollectionTest/full-formatted-message.eml')
        );

        $post = MessageBodyCollection::extract($message);
        $this->assertSame('<html><body><p>Hello World</p></body></html>', $post->getHtml());
        $this->assertSame('Hello World', (string)$post->getText());
        $this->assertCount(1, $post->getEmbeddedImages());
        $this->assertCount(1, $post->getAttachments());
    }

    /**
     * @test
     */
    public function it_extracts_body_from_html_only_messages()
    {
        $message = GenericMessage::fromString(
            \file_get_contents(__DIR__ . '/../Stub/MessageBodyCollectionTest/html-only.eml')
        );

        $post = MessageBodyCollection::extract($message);
        $this->assertSame('<html><body><p>Hello World</p></body></html>', $post->getHtml());
        $this->assertSame('', (string)$post->getText());
        $this->assertCount(0, $post->getEmbeddedImages());
        $this->assertCount(0, $post->getAttachments());
    }

    /**
     * @test
     */
    public function it_extracts_body_from_text_only_messages()
    {
        $message = GenericMessage::fromString(
            \file_get_contents(__DIR__ . '/../Stub/MessageBodyCollectionTest/text-only.eml')
        );

        $post = MessageBodyCollection::extract($message);
        $this->assertSame('', $post->getHtml());
        $this->assertSame('Hello World', (string)$post->getText());
        $this->assertCount(0, $post->getEmbeddedImages());
        $this->assertCount(0, $post->getAttachments());
    }

    /**
     * @test
     */
    public function it_extracts_body_from_html_and_text_messages()
    {
        $message = GenericMessage::fromString(
            \file_get_contents(__DIR__ . '/../Stub/MessageBodyCollectionTest/html-and-text.eml')
        );

        $post = MessageBodyCollection::extract($message);
        $this->assertSame('<html><body><p>Hello World</p></body></html>', $post->getHtml());
        $this->assertSame('Hello World', (string)$post->getText());
        $this->assertCount(0, $post->getEmbeddedImages());
        $this->assertCount(0, $post->getAttachments());
    }

    /**
     * @param string $messageString
     * @param string $boundary
     * @return string
     */
    private function replaceBoundaries(string $messageString, string $boundary): string
    {
        return \preg_replace(['/(GenkgoMailV2Part[A-Za-z0-9\-]*)/'], $boundary, $messageString);
    }
}
