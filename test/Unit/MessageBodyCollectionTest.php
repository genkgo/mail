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
use Genkgo\Mail\Header\From;
use Genkgo\Mail\Header\GenericHeader;
use Genkgo\Mail\Header\MessageId;
use Genkgo\Mail\Header\ReplyTo;
use Genkgo\Mail\Header\Subject;
use Genkgo\Mail\Header\To;
use Genkgo\Mail\MessageBodyCollection;
use Genkgo\Mail\Mime\EmbeddedImage;
use Genkgo\Mail\Mime\HtmlPart;
use Genkgo\Mail\Mime\ResourceAttachment;
use Genkgo\Mail\Quotation\FixedQuotation;
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
            \file_get_contents(__DIR__ . '/../Stub/MessageBodyCollection/html-only.eml'),
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
            \file_get_contents(__DIR__ . '/../Stub/MessageBodyCollection/html-only.eml'),
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
            \file_get_contents(__DIR__ . '/../Stub/MessageBodyCollection/text-only.eml'),
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
            \file_get_contents(__DIR__ . '/../Stub/MessageBodyCollection/empty-email.eml'),
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
                \file_get_contents(__DIR__ . '/../Stub/MessageBodyCollection/full-formatted-message.eml')
            ),
            $this->replaceBoundaries((string) $message)
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
            ->withHeader((new From(new Address(new EmailAddress('me@example.com'), 'me'))))
            ->withHeader((new To(new AddressList([new Address(new EmailAddress('you@example.com'), 'you')]))))
            ->withHeader((new Cc(new AddressList([new Address(new EmailAddress('other@example.com'), 'other')]))));

        $this->assertEquals(
            $this->replaceBoundaries(
                \file_get_contents(__DIR__ . '/../Stub/MessageBodyCollection/html-and-text.eml')
            ),
            $this->replaceBoundaries((string) $message)
        );
    }
    
    /**
     * @test
     */
    public function it_extracts_body_from_full_formatted_messages()
    {
        $message = GenericMessage::fromString(
            \file_get_contents(__DIR__ . '/../Stub/MessageBodyCollection/full-formatted-message.eml')
        );

        $body = MessageBodyCollection::extract($message);
        $this->assertSame('<html><body><p>Hello World</p></body></html>', $body->getHtml());
        $this->assertSame('Hello World', (string)$body->getText());
        $this->assertCount(1, $body->getEmbeddedImages());
        $this->assertCount(1, $body->getAttachments());
    }

    /**
     * @test
     */
    public function it_extracts_body_from_html_only_messages()
    {
        $message = GenericMessage::fromString(
            \file_get_contents(__DIR__ . '/../Stub/MessageBodyCollection/html-only.eml')
        );

        $body = MessageBodyCollection::extract($message);
        $this->assertSame('<html><body><p>Hello World</p></body></html>', $body->getHtml());
        $this->assertSame('', (string)$body->getText());
        $this->assertCount(0, $body->getEmbeddedImages());
        $this->assertCount(0, $body->getAttachments());
    }

    /**
     * @test
     */
    public function it_extracts_body_from_text_only_messages()
    {
        $message = GenericMessage::fromString(
            \file_get_contents(__DIR__ . '/../Stub/MessageBodyCollection/text-only.eml')
        );

        $body = MessageBodyCollection::extract($message);
        $this->assertSame('', $body->getHtml());
        $this->assertSame('Hello World', (string)$body->getText());
        $this->assertCount(0, $body->getEmbeddedImages());
        $this->assertCount(0, $body->getAttachments());
    }

    /**
     * @test
     */
    public function it_extracts_body_from_html_and_text_messages()
    {
        $message = GenericMessage::fromString(
            \file_get_contents(__DIR__ . '/../Stub/MessageBodyCollection/html-and-text.eml')
        );

        $body = MessageBodyCollection::extract($message);
        $this->assertSame('<html><body><p>Hello World</p></body></html>', $body->getHtml());
        $this->assertSame('Hello World', (string)$body->getText());
        $this->assertCount(0, $body->getEmbeddedImages());
        $this->assertCount(0, $body->getAttachments());
    }

    /**
     * @test
     */
    public function it_should_attach_messages()
    {
        $message = GenericMessage::fromString(
            \file_get_contents(__DIR__ . '/../Stub/MessageBodyCollection/html-and-text.eml')
        );

        $body = (new MessageBodyCollection('<html><body><p>Reply text</p></body></html>'))
            ->withAttachedMessage($message);

        $expectedMessage = \file_get_contents(__DIR__ . '/../Stub/MessageBodyCollection/attached-html-and-text.eml');

        $this->assertSame(
            $this->replaceBoundaries($expectedMessage),
            $this->replaceBoundaries((string)$body->createMessage())
        );
    }

    /**
     * @test
     */
    public function it_should_quote_an_original_message()
    {
        $message = GenericMessage::fromString(
            \file_get_contents(__DIR__ . '/../Stub/MessageBodyCollection/html-and-text.eml')
        );

        $body = (new MessageBodyCollection('<html><body><p>Reply text</p></body></html>'))
            ->withQuotedMessage($message, new FixedQuotation());

        $expectedMessage = \file_get_contents(__DIR__ . '/../Stub/MessageBodyCollection/quoted-html-and-text.eml');

        $this->assertSame(
            $this->replaceBoundaries($expectedMessage),
            $this->replaceBoundaries((string)$body->createMessage())
        );
    }

    /**
     * @test
     */
    public function it_should_create_reply_message()
    {
        $message = GenericMessage::fromString(
            \file_get_contents(__DIR__ . '/../Stub/MessageBodyCollection/html-and-text.eml')
        );

        $body = (new MessageBodyCollection('<html><body><p>Reply text</p></body></html>'))
            ->withQuotedMessage($message, new FixedQuotation());

        $expectedMessage = \file_get_contents(__DIR__ . '/../Stub/MessageBodyCollection/reply-html-and-text.eml');

        $this->assertSame(
            $this->replaceBoundaries($expectedMessage),
            $this->replaceBoundaries((string)$body->inReplyTo($message))
        );
    }

    /**
     * @test
     */
    public function it_should_not_reply_to_other_address_than_to()
    {
        $message = GenericMessage::fromString(
            \file_get_contents(__DIR__ . '/../Stub/MessageBodyCollection/html-and-text.eml')
        )
            ->withHeader(new Cc(AddressList::fromString('cc@example.com')));

        $body = (new MessageBodyCollection('<html><body><p>Reply text</p></body></html>'))
            ->withQuotedMessage($message, new FixedQuotation());

        $expectedMessage = \file_get_contents(__DIR__ . '/../Stub/MessageBodyCollection/reply-html-and-text.eml');

        $this->assertSame(
            $this->replaceBoundaries($expectedMessage),
            $this->replaceBoundaries((string)$body->inReplyTo($message))
        );
    }

    /**
     * @test
     */
    public function it_should_use_reply_to_header_when_replying()
    {
        $message = GenericMessage::fromString(
            \file_get_contents(__DIR__ . '/../Stub/MessageBodyCollection/html-and-text.eml')
        )
            ->withHeader(new ReplyTo(AddressList::fromString('other@example.com')));

        $body = (new MessageBodyCollection('<html><body><p>Reply text</p></body></html>'))
            ->withQuotedMessage($message, new FixedQuotation());

        $expectedMessage = \file_get_contents(__DIR__ . '/../Stub/MessageBodyCollection/reply-to-html-and-text.eml');

        $this->assertSame(
            $this->replaceBoundaries($expectedMessage),
            $this->replaceBoundaries((string)$body->inReplyTo($message))
        );
    }

    /**
     * @test
     */
    public function it_should_equal_file_when_replying_all()
    {
        $message = GenericMessage::fromString(
            \file_get_contents(__DIR__ . '/../Stub/MessageBodyCollection/html-and-text.eml')
        )
            ->withHeader(new Cc(AddressList::fromString('cc@example.com')));

        $body = (new MessageBodyCollection('<html><body><p>Reply text</p></body></html>'))
            ->withQuotedMessage($message, new FixedQuotation());

        $expectedMessage = \file_get_contents(__DIR__ . '/../Stub/MessageBodyCollection/reply-all-html-and-text.eml');

        $this->assertSame(
            $this->replaceBoundaries($expectedMessage),
            $this->replaceBoundaries((string)$body->inReplyToAll($message))
        );
    }

    /**
     * @test
     */
    public function it_should_use_reply_to_when_replying_all()
    {
        $message = GenericMessage::fromString(
            \file_get_contents(__DIR__ . '/../Stub/MessageBodyCollection/html-and-text.eml')
        )
            ->withHeader(new ReplyTo(AddressList::fromString('other@example.com')));

        $body = (new MessageBodyCollection('<html><body><p>Reply text</p></body></html>'))
            ->withQuotedMessage($message, new FixedQuotation());

        $expectedMessage = \file_get_contents(__DIR__ . '/../Stub/MessageBodyCollection/reply-to-all-html-and-text.eml');

        $this->assertSame(
            $this->replaceBoundaries($expectedMessage),
            $this->replaceBoundaries((string)$body->inReplyToAll($message))
        );
    }

    /**
     * @test
     */
    public function it_creates_reply_headers()
    {
        $messageId = MessageId::newRandom('domain');

        $message = (new GenericMessage())
            ->withHeader($messageId);

        $body = new MessageBodyCollection();
        $reply = $body->inReplyTo($message);

        $this->assertTrue($reply->hasHeader('In-Reply-To'));
        $this->assertTrue($reply->hasHeader('References'));
        $this->assertSame((string)$messageId->getValue(), (string)$reply->getHeader('References')[0]->getValue());
        $this->assertSame((string)$messageId->getValue(), (string)$reply->getHeader('In-Reply-To')[0]->getValue());
    }

    /**
     * @test
     */
    public function it_adds_reference()
    {
        $messageId = MessageId::newRandom('domain');

        $message = (new GenericMessage())
            ->withHeader($messageId)
            ->withHeader(new GenericHeader('References', '<id@domain.com>'));

        $body = new MessageBodyCollection();
        $reply = $body->inReplyTo($message);

        $this->assertTrue($reply->hasHeader('In-Reply-To'));
        $this->assertTrue($reply->hasHeader('References'));
        $this->assertSame((string)$messageId->getValue(), (string)$reply->getHeader('In-Reply-To')[0]->getValue());
        $this->assertSame(
            '<id@domain.com>, '.(string)$messageId->getValue(),
            (string)$reply->getHeader('References')[0]->getValue()
        );
    }

    /**
     * @test
     */
    public function it_should_create_forward_message()
    {
        $message = GenericMessage::fromString(
            \file_get_contents(__DIR__ . '/../Stub/MessageBodyCollection/html-and-text.eml')
        );

        $body = (new MessageBodyCollection('<html><body><p>Reply text</p></body></html>'))
            ->withQuotedMessage($message, new FixedQuotation());

        $expectedMessage = \file_get_contents(__DIR__ . '/../Stub/MessageBodyCollection/forward-html-and-text.eml');

        $this->assertSame(
            $this->replaceBoundaries($expectedMessage),
            $this->replaceBoundaries((string)$body->asForwardTo($message))
        );
    }
}
