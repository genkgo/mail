<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Mime;

use Genkgo\TestMail\AbstractTestCase;
use Genkgo\Mail\Header\ContentDisposition;
use Genkgo\Mail\Header\ContentID;
use Genkgo\Mail\Header\ContentType;
use Genkgo\Mail\Mime\EmbeddedImage;
use Genkgo\Mail\Stream\Base64EncodedStream;
use Genkgo\Mail\Stream\AsciiEncodedStream;

final class EmbeddedImageTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_is_immutable()
    {
        $part = new EmbeddedImage(
            new AsciiEncodedStream(
                \base64_decode('R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==')
            ),
            'image.png',
            new ContentType('image/png'),
            new ContentID('123456')
        );

        $this->assertNotSame($part, $part->withHeader(new ContentType('text/html')));
        $this->assertNotSame($part, $part->withoutHeader('content/type'));
    }

    /**
     * @test
     */
    public function it_cannot_modify_body()
    {
        $this->expectException(\RuntimeException::class);

        $part = new EmbeddedImage(
            new AsciiEncodedStream(
                \base64_decode('R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==')
            ),
            'image.png',
            new ContentType('image/png'),
            new ContentID('123456')
        );
        $part->withBody(new AsciiEncodedStream('body'));
    }

    /**
     * @test
     */
    public function it_has_header_content_type()
    {
        $part = new EmbeddedImage(
            new AsciiEncodedStream(
                \base64_decode('R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==')
            ),
            'image.png',
            new ContentType('image/png'),
            new ContentID('123456')
        );

        $this->assertTrue($part->hasHeader('content-type'));
        $this->assertEquals(
            'image/png',
            (string)$part->getHeader('content-type')->getValue()
        );
        $this->assertCount(4, $part->getHeaders());
    }

    /**
     * @test
     */
    public function it_throws_when_adding_content_disposition_header()
    {
        $this->expectException(\InvalidArgumentException::class);

        $attachment = new EmbeddedImage(
            new AsciiEncodedStream(
                \base64_decode('R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==')
            ),
            'image.png',
            new ContentType('image/png'),
            new ContentID('123456')
        );

        $attachment->withHeader(ContentDisposition::newAttachment('x'));
    }

    /**
     * @test
     */
    public function it_throws_when_removing_content_disposition_header()
    {
        $this->expectException(\InvalidArgumentException::class);

        $attachment = new EmbeddedImage(
            new AsciiEncodedStream(
                \base64_decode('R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==')
            ),
            'image.png',
            new ContentType('image/png'),
            new ContentID('123456')
        );

        $attachment->withoutHeader('content-disposition');
    }

    /**
     * @test
     */
    public function it_encodes_body_with_base64()
    {
        $attachment = new EmbeddedImage(
            new AsciiEncodedStream(
                \base64_decode('R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==')
            ),
            'image.png',
            new ContentType('image/png'),
            new ContentID('123456')
        );

        $this->assertInstanceOf(Base64EncodedStream::class, $attachment->getBody());
    }
}
