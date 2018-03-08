<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Stream;

use Genkgo\TestMail\AbstractTestCase;
use Genkgo\Mail\Stream\QuotedPrintableStream;

final class QuotedPrintableStreamTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_produces_equally_result_with_to_string_and_read()
    {
        $decoded = \str_repeat('test1 test2', 50);
        $stream = QuotedPrintableStream::fromString($decoded);

        $encoded = '';
        while (!$stream->eof()) {
            $encoded .= $stream->read(4);
        }

        $this->assertEquals($decoded, \quoted_printable_decode((string)$stream));
        $this->assertEquals($decoded, \quoted_printable_decode($encoded));
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

        $this->assertEquals(\substr(\quoted_printable_encode($decoded), 2), $stream->getContents());
    }

    /**
     * @test
     */
    public function it_is_rewindable()
    {
        $decoded = 'tëst1 test2';

        $stream = QuotedPrintableStream::fromString($decoded);
        $stream->rewind();

        $this->assertEquals(\quoted_printable_encode($decoded), $stream->getContents());
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

        $stream = new QuotedPrintableStream(\fopen('php://memory', 'r+'));
        $this->assertFalse($stream->isWritable());

        $stream->write('x');
    }

    /**
     * @test
     */
    public function it_uses_correct_max_lines()
    {
        $decoded = \str_repeat('tëst1 test2', 50);
        $stream = QuotedPrintableStream::fromString($decoded);

        $lines = \preg_split('/\r\n/', (string)$stream);

        $lines = \array_map(
            function ($line) {
                return \strlen($line);
            },
            $lines
        );

        $this->assertLessThanOrEqual(78, \max($lines));
    }

    /**
     * @test
     */
    public function it_uses_correct_line_endings_to_string()
    {
        $decoded = \str_repeat("tëst1 test2\n", 50);
        $stream = QuotedPrintableStream::fromString($decoded);
        $encoded = (string)$stream;

        $this->assertEquals(\str_repeat("tëst1 test2\r\n", 50), \quoted_printable_decode($encoded));
    }

    /**
     * @test
     */
    public function it_uses_correct_line_endings_read()
    {
        $decoded = \str_repeat("tëst1 test2\n", 50);
        $stream = QuotedPrintableStream::fromString($decoded);

        $encoded = '';
        while (!$stream->eof()) {
            $encoded .= $stream->read(4);
        }

        $this->assertEquals(\str_repeat("tëst1 test2\r\n", 50), \quoted_printable_decode($encoded));
    }

    /**
     * @test
     */
    public function it_uses_correct_line_endings_tab()
    {
        $decoded = \str_repeat("tëst1\ttest2\t\n", 50);
        $stream = QuotedPrintableStream::fromString($decoded);

        $encoded = '';
        while (!$stream->eof()) {
            $encoded .= $stream->read(4);
        }

        $this->assertEquals(\str_repeat("tëst1\ttest2\r\n", 50), \quoted_printable_decode($encoded));
    }

    /**
     * @test
     */
    public function it_produces_equally_result_with_to_string_twice()
    {
        $stream = QuotedPrintableStream::fromString(
            \file_get_contents(__DIR__.'/../../Stub/BugReport/issue-30.txt')
        );

        $this->assertEquals((string)$stream, (string)$stream);
    }
}
