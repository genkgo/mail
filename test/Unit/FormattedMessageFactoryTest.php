<?php

namespace Genkgo\Mail\Unit;

use Genkgo\Mail\AbstractTestCase;
use Genkgo\Mail\Address;
use Genkgo\Mail\EmailAddress;
use Genkgo\Mail\FormattedMessageFactory;
use Genkgo\Mail\Header\Cc;
use Genkgo\Mail\Header\ContentID;
use Genkgo\Mail\Header\ContentType;
use Genkgo\Mail\Header\Subject;
use Genkgo\Mail\Header\To;
use Genkgo\Mail\Mime\EmbeddedImagePart;
use Genkgo\Mail\Mime\StringAttachment;
use Genkgo\Mail\Stream\StringStream;

/**
 * Class FormattedMessageFactoryTest
 * @package Genkgo\Email\Unit
 */
final class FormattedMessageFactoryTest extends AbstractTestCase
{

    /**
     * @test
     */
    public function it_should_equal_file_when_html_only ()
    {
        $factory = (new FormattedMessageFactory())
            ->withHtmlAndNoGeneratedAlternativeText('<html><body><p>Hello World</p></body></html>')
        ;

        $message = $factory->createMessage()
            ->withHeader(new Subject('Hello World'))
            ->withHeader((new To([new Address(new EmailAddress('me@example.com'), 'me')])))
            ->withHeader((new Cc([new Address(new EmailAddress('other@example.com'), 'other')])))
        ;

        $this->assertEquals(
            file_get_contents(__DIR__ . '/../Stubs/FormattedMessageFactoryTest/html-only.txt'),
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
                new StringAttachment(
                    'Attachment text',
                    'attachment.txt',
                    new ContentType('plain/text')
                )
            )
            ->withEmbeddedImage(
                new EmbeddedImagePart(
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

        $this->assertEquals(
            $this->replaceBoundaries(
                file_get_contents(__DIR__ . '/../Stubs/FormattedMessageFactoryTest/full-formatted-message.txt'),
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
    private function replaceBoundaries($messageString, $boundary): string
    {
        return preg_replace(['/(_part_[A-Za-z0-9\-]*)/'], $boundary, $messageString);
    }

}