<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Stream;

use Genkgo\TestMail\AbstractTestCase;
use Genkgo\Mail\Stream\ConcatenatedStream;
use Genkgo\Mail\Stream\AsciiEncodedStream;

final class ConcatenatedStreamTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_produces_equally_result_with_to_string_and_read()
    {
        $stream = new ConcatenatedStream(
            new \ArrayObject([
                new AsciiEncodedStream('test1'),
                new AsciiEncodedStream('test2'),
            ])
        );

        $streamRead = '';
        while (!$stream->eof()) {
            $streamRead .= $stream->read(8194);
        }

        $this->assertEquals($streamRead, (string)$stream);
    }

    /**
     * @test
     */
    public function it_has_a_correct_size()
    {
        $stream = new ConcatenatedStream(
            new \ArrayObject([
                new AsciiEncodedStream('test1'),
                new AsciiEncodedStream('test2'),
            ])
        );

        $this->assertEquals(10, $stream->getSize());
    }

    /**
     * @test
     */
    public function it_reads_remaining_contents()
    {
        $stream = new ConcatenatedStream(
            new \ArrayObject([
                new AsciiEncodedStream('test1'),
                new AsciiEncodedStream('test2'),
            ])
        );

        $stream->read(2);

        $this->assertEquals('st1test2', $stream->getContents());
    }

    /**
     * @test
     */
    public function it_is_rewindable()
    {
        $stream = new ConcatenatedStream(
            new \ArrayObject([
                new AsciiEncodedStream('test1'),
                new AsciiEncodedStream('test2'),
            ])
        );

        $stream->read(2);
        $stream->rewind();

        $this->assertEquals('test1test2', $stream->getContents());
    }

    /**
     * @test
     */
    public function it_can_seek()
    {
        $stream = new ConcatenatedStream(
            new \ArrayObject([
                new AsciiEncodedStream('test1'),
                new AsciiEncodedStream('test2'),
            ])
        );

        $stream->seek(8);

        $this->assertEquals(8, $stream->tell());
        $this->assertEquals('t2', $stream->getContents());
    }

    /**
     * @test
     */
    public function it_cannot_be_written_to()
    {
        $this->expectException(\RuntimeException::class);

        $stream = new ConcatenatedStream(
            new \ArrayObject([
                new AsciiEncodedStream('test1'),
                new AsciiEncodedStream('test2'),
            ])
        );

        $this->assertFalse($stream->isWritable());
        $stream->write('x');
    }
}
