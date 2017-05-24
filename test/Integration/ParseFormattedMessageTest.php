<?php

namespace Genkgo\Mail\Integration;

use Genkgo\Mail\AbstractTestCase;
use Genkgo\Mail\Address;
use Genkgo\Mail\EmailAddress;
use Genkgo\Mail\FormattedMessageFactory;
use Genkgo\Mail\GenericMessage;
use Genkgo\Mail\Header\Cc;
use Genkgo\Mail\Header\ContentID;
use Genkgo\Mail\Header\ContentType;
use Genkgo\Mail\Header\Subject;
use Genkgo\Mail\Header\To;
use Genkgo\Mail\Mime\EmbeddedImage;
use Genkgo\Mail\Mime\StringAttachment;
use Genkgo\Mail\Stream\StringStream;

/**
 * Class ParseFormattedMessageTest
 * @package Genkgo\Email\Integration
 */
final class ParseFormattedMessageTest extends AbstractTestCase
{

    /**
     * @test
     */
    public function it_can_parse_a_formatted_message_string_into_a_generic_message()
    {
        $message = (new FormattedMessageFactory())
            ->withHtml('<html><body><p>Hello World</p></body></html>')
            ->withAttachment(
                new StringAttachment(
                    'Attachment text',
                    'attachment.txt',
                    new ContentType('plain/text')
                )
            )
            ->withEmbeddedImage(
                new EmbeddedImage(
                    new StringStream(
                        base64_decode('R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==')
                    ),
                    'pixel.gif',
                    new ContentType('image/gif'),
                    new ContentID('123456')
                )
            )
            ->createMessage()
            ->withHeader(new Subject('Hello World'))
            ->withHeader((new To([new Address(new EmailAddress('me@example.com'), 'me')])))
            ->withHeader((new Cc([new Address(new EmailAddress('other@example.com'), 'other')])))
        ;

        $messageString = (string) $message;

        $this->assertEquals(
            $messageString,
            (string) GenericMessage::fromString($messageString)
        );
    }

}