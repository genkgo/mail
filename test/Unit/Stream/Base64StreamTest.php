<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Stream;

use Genkgo\TestMail\AbstractTestCase;
use Genkgo\Mail\Stream\Base64EncodedStream;

final class Base64StreamTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_produces_equally_result_with_to_string_and_read()
    {
        $decoded = \str_repeat('test1 test2', 50);

        $stream = Base64EncodedStream::fromString($decoded);

        $streamRead = '';
        while (!$stream->eof()) {
            $streamRead .= $stream->read(4);
        }

        $this->assertEquals($decoded, \base64_decode((string)$stream));
        $this->assertEquals($decoded, \base64_decode($streamRead));
    }

    /**
     * @test
     */
    public function it_has_a_correct_size()
    {
        $decoded = 'test1test2';

        $stream = Base64EncodedStream::fromString($decoded);

        $this->assertEquals(16, $stream->getSize());
        $this->assertEquals($stream->getSize(), \strlen((string)$stream));
    }

    /**
     * @test
     */
    public function it_reads_remaining_contents()
    {
        $decoded = 'test1test2';

        $stream = Base64EncodedStream::fromString($decoded);
        $stream->read(2);

        $this->assertEquals(\substr(\base64_encode($decoded), 2), $stream->getContents());
    }

    /**
     * @test
     */
    public function it_is_rewindable()
    {
        $decoded = 'test1test2';

        $stream = Base64EncodedStream::fromString($decoded);

        $stream->rewind();

        $this->assertEquals(\base64_encode($decoded), $stream->getContents());
    }

    /**
     * @test
     */
    public function it_can_seek()
    {
        $decoded = 'test1test2';

        $stream = Base64EncodedStream::fromString($decoded);

        $this->assertEquals(-1, $stream->seek(4));
        $this->assertEquals(0, $stream->tell());
    }

    /**
     * @test
     */
    public function it_cannot_be_written_to()
    {
        $this->expectException(\RuntimeException::class);

        $stream = new Base64EncodedStream(\fopen('php://memory', 'r+'));
        $this->assertFalse($stream->isWritable());

        $stream->write('x');
    }

    /**
     * @test
     */
    public function it_uses_correct_line_endings()
    {
        $decoded = \str_repeat('test1 test2', 50);

        $stream = Base64EncodedStream::fromString($decoded);

        $lines = \preg_split('/\r\n/', (string)$stream);

        $lines = \array_map(
            function ($line) {
                return \strlen($line);
            },
            $lines
        );

        $this->assertLessThanOrEqual(76, \max($lines));
    }

    /**
     * @test
     */
    public function it_produces_equally_result_with_to_string_twice()
    {
        $stream = Base64EncodedStream::fromString(
            \file_get_contents(__DIR__.'/../../Stub/BugReport/issue-30.txt')
        );

        $this->assertEquals((string)$stream, (string)$stream);
    }
}
