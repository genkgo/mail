<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Stream;

use Genkgo\TestMail\AbstractTestCase;
use Genkgo\Mail\Stream\EmptyStream;

final class EmptyStreamTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_produces_equally_result_with_to_string_and_read()
    {
        $stream = new EmptyStream();

        $streamRead = '';
        while (!$stream->eof()) {
            $streamRead .= $stream->read(8194);
        }

        $this->assertEquals($streamRead, (string)$stream);
    }

    /**
     * @test
     */
    public function it_is_always_eof()
    {
        $stream = new EmptyStream();

        $this->assertTrue($stream->eof());
    }

    /**
     * @test
     */
    public function it_has_a_correct_size()
    {
        $stream = new EmptyStream();

        $this->assertEquals(0, $stream->getSize());
    }

    /**
     * @test
     */
    public function it_reads_remaining_contents()
    {
        $stream = new EmptyStream();

        $stream->read(2);

        $this->assertEquals('', $stream->getContents());
    }

    /**
     * @test
     */
    public function it_is_rewindable()
    {
        $stream = new EmptyStream();

        $stream->read(2);
        $stream->rewind();

        $this->assertEquals('', $stream->getContents());
    }

    /**
     * @test
     */
    public function it_can_seek()
    {
        $stream = new EmptyStream();

        $stream->seek(3);

        $this->assertEquals(0, $stream->tell());
        $this->assertEquals('', $stream->getContents());
    }

    /**
     * @test
     */
    public function it_cannot_be_written_to()
    {
        $this->expectException(\RuntimeException::class);

        $stream = new EmptyStream();
        $this->assertFalse($stream->isWritable());

        $stream->write('x');
    }
}
