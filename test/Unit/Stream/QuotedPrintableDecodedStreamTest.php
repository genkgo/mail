<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Stream;

use Genkgo\Mail\Stream\QuotedPrintableDecodedStream;
use Genkgo\TestMail\AbstractTestCase;

final class QuotedPrintableDecodedStreamTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_decodes_a_string(): void
    {
        $encoded = 't=C3=ABst1 test2';

        $stream = QuotedPrintableDecodedStream::fromString($encoded);

        $this->assertEquals('tëst1 test2', (string)$stream);
    }

    /**
     * @test
     */
    public function it_has_a_correct_size(): void
    {
        $encoded = 't=C3=ABst1 test2';

        $stream = QuotedPrintableDecodedStream::fromString($encoded);

        $this->assertNull($stream->getSize());
    }

    /**
     * @test
     */
    public function it_reads_remaining_contents(): void
    {
        $encoded = 't=3Fst1 test2';

        $stream = QuotedPrintableDecodedStream::fromString($encoded);

        $this->assertEquals('t?s', $stream->read(3));
        $this->assertEquals('t1 test2', $stream->getContents());
    }

    /**
     * @test
     */
    public function it_reads_remaining_utf8_contents(): void
    {
        $encoded = 't=C3=ABst1 test2';

        $stream = QuotedPrintableDecodedStream::fromString($encoded);

        $this->assertEquals('të', $stream->read(3));
        $this->assertEquals('st1 test2', $stream->getContents());
    }

    /**
     * @test
     */
    public function it_is_rewindable(): void
    {
        $encoded = 't=C3=ABst1 test2';

        $stream = QuotedPrintableDecodedStream::fromString($encoded);
        $stream->rewind();

        $this->assertEquals('tëst1 test2', $stream->getContents());
    }

    /**
     * @test
     */
    public function it_can_seek(): void
    {
        $encoded = 't=C3=ABst1 test2';

        $stream = QuotedPrintableDecodedStream::fromString($encoded);

        $this->assertEquals(-1, $stream->seek(4));
        $this->assertEquals(0, $stream->tell());
    }

    /**
     * @test
     */
    public function it_cannot_be_written_to(): void
    {
        $this->expectException(\RuntimeException::class);

        $encoded = 't=C3=ABst1 test2';

        $stream = QuotedPrintableDecodedStream::fromString($encoded);

        $this->assertFalse($stream->isWritable());
        $stream->write('x');
    }
}
