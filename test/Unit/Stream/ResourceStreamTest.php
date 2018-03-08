<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Stream;

use Genkgo\TestMail\AbstractTestCase;
use Genkgo\Mail\Stream\ResourceStream;

final class ResourceStreamTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_produces_equally_result_with_to_string_and_read()
    {
        $resource = \fopen('php://memory', 'r+');
        \fwrite($resource, 'test1test2');

        $stream = new ResourceStream($resource);

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
        $resource = \fopen('php://memory', 'r+');
        \fwrite($resource, 'test1test2');

        $stream = new ResourceStream($resource);

        $this->assertEquals(10, $stream->getSize());
    }

    /**
     * @test
     */
    public function it_reads_remaining_contents()
    {
        $resource = \fopen('php://memory', 'r+');
        \fwrite($resource, 'test1test2');

        $stream = new ResourceStream($resource);

        $stream->read(2);

        $this->assertEquals('st1test2', $stream->getContents());
    }

    /**
     * @test
     */
    public function it_is_rewindable()
    {
        $resource = \fopen('php://memory', 'r+');
        \fwrite($resource, 'test1test2');

        $stream = new ResourceStream($resource);

        $stream->read(2);
        $stream->rewind();

        $this->assertEquals('test1test2', $stream->getContents());
    }

    /**
     * @test
     */
    public function it_can_seek()
    {
        $resource = \fopen('php://memory', 'r+');
        \fwrite($resource, 'test1test2');

        $stream = new ResourceStream($resource);

        $stream->seek(3);

        $this->assertEquals(3, $stream->tell());
        $this->assertEquals('t1test2', $stream->getContents());
    }

    /**
     * @test
     */
    public function it_can_be_written_to()
    {
        $resource = \fopen('php://memory', 'r+');
        \fwrite($resource, 'test1test2');

        $stream = new ResourceStream($resource);

        $this->assertTrue($stream->isWritable());

        $stream->write('x');

        $this->assertEquals('est1test2', $stream->getContents());
        $this->assertEquals('xest1test2', (string) $stream);
    }
}
