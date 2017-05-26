<?php

namespace Genkgo\Mail\Unit\Stream;

use Genkgo\Mail\AbstractTestCase;
use Genkgo\Mail\Stream\QuotedPrintableStream;

final class QuotedPrintableStreamTest extends AbstractTestCase
{

    /**
     * @test
     */
    public function it_produces_equally_result_with_to_string_and_read()
    {
        $decoded = str_repeat('test1 test2', 50);
        $stream = QuotedPrintableStream::fromString($decoded);

        $streamRead = '';
        while (!$stream->eof()) {
            $streamRead .= $stream->read(4);
        }

        $this->assertEquals($decoded, quoted_printable_decode((string)$stream));
        $this->assertEquals($decoded, quoted_printable_decode($streamRead));
    }

    /**
     * @test
     */
    public function it_has_a_correct_size()
    {
        $stream = QuotedPrintableStream::fromString('tëst1test2');

        $this->assertNull($stream->getSize());
    }

    /**
     * @test
     */
    public function it_reads_remaining_contents()
    {
        $decoded = 'tëst1 test2';

        $stream = QuotedPrintableStream::fromString($decoded);
        $stream->read(2);

        $this->assertEquals(substr(quoted_printable_encode($decoded), 2), $stream->getContents());
    }

    /**
     * @test
     */
    public function it_is_rewindable()
    {
        $decoded = 'tëst1 test2';

        $stream = QuotedPrintableStream::fromString($decoded);
        $stream->rewind();

        $this->assertEquals(quoted_printable_encode($decoded), $stream->getContents());
    }

    /**
     * @test
     */
    public function it_can_seek()
    {
        $stream = QuotedPrintableStream::fromString('test1test2');

        $this->assertEquals(-1, $stream->seek(4));
        $this->assertEquals(0, $stream->tell());
    }

    /**
     * @test
     */
    public function it_cannot_be_written_to()
    {
        $this->expectException(\RuntimeException::class);

        $stream = new QuotedPrintableStream(fopen('php://memory', 'r+'));
        $this->assertFalse($stream->isWritable());

        $stream->write('x');
    }

    /**
     * @test
     */
    public function it_uses_correct_line_endings()
    {
        $decoded = str_repeat('tëst1 test2', 50);
        $stream = QuotedPrintableStream::fromString($decoded);

        $lines = preg_split('/\r\n/', (string)$stream);

        $lines = array_map(
            function ($line) {
                return strlen($line);
            },
            $lines
        );

        $this->assertLessThanOrEqual(78, max($lines));
    }

}