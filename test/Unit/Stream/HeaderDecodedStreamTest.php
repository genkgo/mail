<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Stream;

use Genkgo\Mail\Header\ContentType;
use Genkgo\Mail\Header\Subject;
use Genkgo\Mail\Mime\FileAttachment;
use Genkgo\Mail\Stream\HeaderDecodedStream;
use Genkgo\TestMail\AbstractTestCase;

final class HeaderDecodedStreamTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_decodes_the_stream(): void
    {
        $stream = new HeaderDecodedStream(new Subject('test'));

        $this->assertEquals('test', (string)$stream);
    }

    /**
     * @test
     */
    public function it_decodes_quoted_printable_headers(): void
    {
        $stream = new HeaderDecodedStream(new Subject('tëst'));

        $this->assertEquals('tëst', (string)$stream);
    }

    /**
     * @test
     */
    public function it_decodes_base64_headers(): void
    {
        $stream = new HeaderDecodedStream(new Subject('tëst tëst tëst tëst tëst tëst tëst'));

        $this->assertEquals('tëst tëst tëst tëst tëst tëst tëst', (string)$stream);
    }

    /**
     * @test
     */
    public function it_has_a_correct_size(): void
    {
        $headerString = 'tëst tëst tëst tëst tëst tëst tëst';

        $stream = new HeaderDecodedStream(new Subject($headerString));

        $this->assertEquals(\strlen($headerString), $stream->getSize());
    }

    /**
     * @test
     */
    public function it_reads_remaining_contents(): void
    {
        $headerString = 'tëst tëst tëst tëst tëst tëst tëst';

        $stream = new HeaderDecodedStream(new Subject($headerString));

        $contents = $stream->read(2);
        $contents .= $stream->getContents();

        $this->assertEquals($headerString, $contents);
    }

    /**
     * @test
     */
    public function it_is_rewindable(): void
    {
        $headerString = 'tëst tëst tëst tëst tëst tëst tëst';

        $stream = new HeaderDecodedStream(new Subject($headerString));

        $stream->rewind();

        $this->assertEquals($headerString, $stream->getContents());
    }

    /**
     * @test
     */
    public function it_can_seek(): void
    {
        $headerString = 'tëst tëst tëst tëst tëst tëst tëst';

        $stream = new HeaderDecodedStream(new Subject($headerString));

        $this->assertEquals(0, $stream->seek(4));
        $this->assertEquals(4, $stream->tell());

        $this->assertEquals('t tëst tëst tëst tëst tëst tëst', $stream->getContents());
    }

    /**
     * @test
     */
    public function it_cannot_be_written_to(): void
    {
        $this->expectException(\RuntimeException::class);

        $headerString = 'tëst tëst tëst tëst tëst tëst tëst';

        $stream = new HeaderDecodedStream(new Subject($headerString));

        $this->assertFalse($stream->isWritable());

        $stream->write('x');
    }
}
