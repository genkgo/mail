<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Mime;

use Genkgo\TestMail\AbstractTestCase;
use Genkgo\Mail\Header\ContentDisposition;
use Genkgo\Mail\Header\ContentType;
use Genkgo\Mail\Mime\FileAttachment;
use Genkgo\Mail\Stream\Base64EncodedStream;
use Genkgo\Mail\Stream\AsciiEncodedStream;

final class FileAttachmentTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_is_immutable()
    {
        $file = \sys_get_temp_dir() . '/attachment.txt';
        \file_put_contents($file, 'content');

        $part = new FileAttachment($file, new ContentType('text/plain'));

        $this->assertNotSame($part, $part->withHeader(new ContentType('text/html')));
        $this->assertNotSame($part, $part->withoutHeader('content/type'));
    }

    /**
     * @test
     */
    public function it_cannot_modify_body()
    {
        $this->expectException(\RuntimeException::class);

        $file = \sys_get_temp_dir() . '/attachment.txt';
        \file_put_contents($file, 'content');

        $part = new FileAttachment($file, new ContentType('text/plain'));
        $part->withBody(new AsciiEncodedStream('body'));
    }

    /**
     * @test
     */
    public function it_has_header_content_type()
    {
        $file = \sys_get_temp_dir() . '/attachment.txt';
        \file_put_contents($file, 'content');

        $part = new FileAttachment($file, new ContentType('image/png'));

        $this->assertTrue($part->hasHeader('content-type'));
        $this->assertEquals(
            'image/png',
            (string)$part->getHeader('content-type')->getValue()
        );

        $this->assertCount(3, $part->getHeaders());
    }

    /**
     * @test
     */
    public function it_throws_when_file_not_exists()
    {
        $this->expectException(\InvalidArgumentException::class);

        new FileAttachment('somewhere.txt', new ContentType('text/plain'));
    }

    /**
     * @test
     */
    public function it_throws_when_adding_content_disposition_header()
    {
        $this->expectException(\InvalidArgumentException::class);

        $file = \sys_get_temp_dir() . '/attachment.txt';
        \file_put_contents($file, 'content');

        $attachment = new FileAttachment($file, new ContentType('image/png'));

        $attachment->withHeader(ContentDisposition::newAttachment('x'));
    }

    /**
     * @test
     */
    public function it_throws_when_removing_content_disposition_header()
    {
        $this->expectException(\InvalidArgumentException::class);

        $file = \sys_get_temp_dir() . '/attachment.txt';
        \file_put_contents($file, 'content');

        $attachment = new FileAttachment($file, new ContentType('image/png'));

        $attachment->withoutHeader('content-disposition');
    }

    /**
     * @test
     */
    public function it_encodes_body_with_base64()
    {
        $file = \sys_get_temp_dir() . '/attachment.txt';
        \file_put_contents($file, 'content');

        $attachment = new FileAttachment($file, new ContentType('image/png'));

        $this->assertInstanceOf(Base64EncodedStream::class, $attachment->getBody());
    }

    /**
     * @test
     */
    public function it_is_able_to_detect_mime_type()
    {
        $attachment = FileAttachment::fromUnknownFileType(
            __DIR__ .'/../../Stub/minimal.pdf'
        );

        $this->assertEquals(
            'application/pdf; charset=utf-8',
            (string) $attachment->getHeader('Content-Type')->getValue()
        );
    }
}
