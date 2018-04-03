<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Integration;

use Genkgo\Mail\MessageBodyCollection;
use Genkgo\TestMail\AbstractTestCase;
use Genkgo\Mail\Header\ContentID;
use Genkgo\Mail\Header\ContentType;
use Genkgo\Mail\Mime\EmbeddedImage;
use Genkgo\Mail\Mime\ResourceAttachment;
use Genkgo\Mail\Stream\AsciiEncodedStream;

final class MessageBodyCollectionTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_can_parse_a_formatted_message_string_into_a_generic_message()
    {
        $message = (new MessageBodyCollection())
            ->withHtml('<html><body><p>Hello World</p></body></html>')
            ->withAttachment(
                ResourceAttachment::fromString(
                    'Attachment text',
                    'attachment.txt',
                    new ContentType('plain/text')
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
            ->createMessage();

        $this->assertEquals(
            $this->replaceBoundaries((string) $message->getBody()),
            $this->replaceBoundaries(
                (string) MessageBodyCollection::extract($message)->createMessage()->getBody()
            )
        );
    }
}
