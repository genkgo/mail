<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Stream;

use Genkgo\Mail\Stream\Base64DecodedStream;
use Genkgo\TestMail\AbstractTestCase;

final class Base64DecodedStreamTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_produces_equally_result_with_to_string_and_read(): void
    {
        $encoded = \base64_encode('test1 test2');

        $stream = Base64DecodedStream::fromString($encoded);

        $this->assertEquals('test1 test2', (string)$stream);
    }

    /**
     * @test
     */
    public function it_has_a_correct_size(): void
    {
        $encoded = \base64_encode('test1 test2');

        $stream = Base64DecodedStream::fromString($encoded);

        $this->assertEquals(11, $stream->getSize());
        $this->assertEquals($stream->getSize(), \strlen((string)$stream));
    }

    /**
     * @test
     */
    public function it_reads_remaining_contents(): void
    {
        $encoded = \base64_encode('test1test2');

        $stream = Base64DecodedStream::fromString($encoded);

        $this->assertEquals('te', $stream->read(2));
        $this->assertEquals('st1test2', $stream->getContents());
    }

    /**
     * @test
     */
    public function it_is_rewindable(): void
    {
        $encoded = \base64_encode('test1test2');

        $stream = Base64DecodedStream::fromString($encoded);

        $stream->rewind();

        $this->assertEquals(\base64_decode($encoded), $stream->getContents());
    }

    /**
     * @test
     */
    public function it_can_seek(): void
    {
        $encoded = \base64_encode('test1test2');

        $stream = Base64DecodedStream::fromString($encoded);

        $this->assertEquals(-1, $stream->seek(4));
        $this->assertEquals(0, $stream->tell());
    }

    /**
     * @test
     */
    public function it_cannot_be_written_to(): void
    {
        $this->expectException(\RuntimeException::class);

        $encoded = \base64_encode('test1test2');

        $stream = Base64DecodedStream::fromString($encoded);

        $this->assertFalse($stream->isWritable());

        $stream->write('x');
    }
}
