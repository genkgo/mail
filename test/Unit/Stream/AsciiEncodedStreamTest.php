<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Stream;

use Genkgo\TestMail\AbstractTestCase;
use Genkgo\Mail\Stream\AsciiEncodedStream;

final class AsciiEncodedStreamTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_produces_equally_result_with_to_string_and_read(): void
    {
        $stream = new AsciiEncodedStream('test1');

        $streamRead = '';
        while (!$stream->eof()) {
            $streamRead .= $stream->read(8194);
        }

        $this->assertEquals($streamRead, (string)$stream);
    }

    /**
     * @test
     */
    public function it_folds_lines(): void
    {
        $value = \str_repeat('test1', 50);

        $stream = new AsciiEncodedStream($value);

        $this->assertEquals(\wordwrap($value, 78), (string)$stream);
    }

    /**
     * @test
     */
    public function it_has_a_correct_size(): void
    {
        $stream = new AsciiEncodedStream('test1');

        $this->assertEquals(5, $stream->getSize());
    }

    /**
     * @test
     */
    public function it_reads_remaining_contents(): void
    {
        $stream = new AsciiEncodedStream('test1');

        $stream->read(2);

        $this->assertEquals('st1', $stream->getContents());
    }

    /**
     * @test
     */
    public function it_is_rewindable(): void
    {
        $stream = new AsciiEncodedStream('test1');

        $stream->read(2);
        $stream->rewind();

        $this->assertEquals('test1', $stream->getContents());
    }

    /**
     * @test
     */
    public function it_can_seek(): void
    {
        $stream = new AsciiEncodedStream('test1');

        $stream->seek(3);

        $this->assertEquals(3, $stream->tell());
        $this->assertEquals('t1', $stream->getContents());
    }

    /**
     * @test
     */
    public function it_can_be_written_to(): void
    {
        $stream = new AsciiEncodedStream('test1');
        $this->assertTrue($stream->isWritable());

        $stream->write('x');

        $this->assertEquals('est1', $stream->getContents());
        $this->assertEquals('xest1', (string) $stream);
    }

    /**
     * @test
     */
    public function it_wraps_words(): void
    {
        $stream = new AsciiEncodedStream("Hello World Hello World", 11);

        $this->assertEquals("Hello World\r\nHello World", $stream->getContents());
    }
}
