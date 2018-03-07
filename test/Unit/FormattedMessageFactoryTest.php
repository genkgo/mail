<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit;

use Genkgo\Mail\AlternativeText;
use Genkgo\TestMail\AbstractTestCase;
use Genkgo\Mail\FormattedMessageFactory;
use Genkgo\Mail\Header\ContentID;
use Genkgo\Mail\Header\ContentType;
use Genkgo\Mail\Mime\EmbeddedImage;
use Genkgo\Mail\Mime\ResourceAttachment;
use Genkgo\Mail\Stream\AsciiEncodedStream;

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
}
