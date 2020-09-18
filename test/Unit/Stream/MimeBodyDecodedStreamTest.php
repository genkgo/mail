<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Stream;

use Genkgo\Mail\Header\ContentType;
use Genkgo\Mail\Mime\FileAttachment;
use Genkgo\Mail\Stream\Base64DecodedStream;
use Genkgo\Mail\Stream\MimeBodyDecodedStream;
use Genkgo\TestMail\AbstractTestCase;

final class MimeBodyDecodedStreamTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_decodes_the_stream(): void
    {
        $file = __DIR__ . '/../../Stub/minimal.pdf';
        $part = new FileAttachment($file, new ContentType('application/pdf'));

        $stream = new MimeBodyDecodedStream($part);

        $this->assertEquals(\file_get_contents($file), (string)$stream);
    }

    /**
     * @test
     */
    public function it_has_a_correct_size(): void
    {
        $file = __DIR__ . '/../../Stub/minimal.pdf';
        $part = new FileAttachment($file, new ContentType('application/pdf'));

        $stream = new MimeBodyDecodedStream($part);

        $this->assertEquals(\filesize($file), $stream->getSize());
    }

    /**
     * @test
     */
    public function it_reads_remaining_contents(): void
    {
        $file = __DIR__ . '/../../Stub/minimal.pdf';
        $part = new FileAttachment($file, new ContentType('application/pdf'));

        $stream = new MimeBodyDecodedStream($part);

        $contents = $stream->read(2);
        $contents .= $stream->getContents();

        $this->assertEquals(\file_get_contents($file), $contents);
    }

    /**
     * @test
     */
    public function it_is_rewindable(): void
    {
        $file = __DIR__ . '/../../Stub/minimal.pdf';
        $part = new FileAttachment($file, new ContentType('application/pdf'));

        $stream = new MimeBodyDecodedStream($part);

        $stream->rewind();

        $this->assertEquals(\file_get_contents($file), $stream->getContents());
    }

    /**
     * @test
     */
    public function it_can_seek(): void
    {
        $file = __DIR__ . '/../../Stub/minimal.pdf';
        $part = new FileAttachment($file, new ContentType('application/pdf'));

        $stream = new MimeBodyDecodedStream($part);

        $this->assertEquals(-1, $stream->seek(4));
        $this->assertEquals(0, $stream->tell());
    }

    /**
     * @test
     */
    public function it_cannot_be_written_to(): void
    {
        $this->expectException(\RuntimeException::class);

        $file = __DIR__ . '/../../Stub/minimal.pdf';
        $part = new FileAttachment($file, new ContentType('application/pdf'));

        $stream = new MimeBodyDecodedStream($part);

        $this->assertFalse($stream->isWritable());

        $stream->write('x');
    }
}
