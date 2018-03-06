<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Queue;

use Genkgo\TestMail\AbstractTestCase;
use Genkgo\Mail\Exception\EmptyQueueException;
use Genkgo\Mail\GenericMessage;
use Genkgo\Mail\Header\Date;
use Genkgo\Mail\Queue\FilesystemQueue;

final class FilesystemQueueTest extends AbstractTestCase
{
    public function setUp()
    {
        $directory = \sys_get_temp_dir();
        $iterator = new \GlobIterator($directory . '/*.eml');
        foreach ($iterator as $key => $file) {
            \unlink($file->getPathname());
        }
    }

    /**
     * @test
     */
    public function it_can_store_a_message_onto_the_filesystem()
    {
        $directory = \sys_get_temp_dir();

        $message = (new GenericMessage())
            ->withHeader(new Date(new \DateTimeImmutable('2017-01-01 18:15:00')));

        $queue = new FilesystemQueue($directory);
        $queue->store($message);

        $filename = \hash('sha256', (string) $message) . '.eml';

        $this->assertTrue(\file_exists($directory . '/' . $filename));
        $this->assertEquals(
            (string) $message,
            \file_get_contents($directory . '/' . $filename)
        );
    }

    /**
     * @test
     */
    public function it_can_fetch_a_message_from_the_filesystem()
    {
        $directory = \sys_get_temp_dir();

        $message = (new GenericMessage())
            ->withHeader(new Date(new \DateTimeImmutable('2017-01-01 18:15:00')));

        $queue = new FilesystemQueue($directory);
        $queue->store($message);

        $fetchedMessage = $queue->fetch();

        $this->assertNotSame($message, $fetchedMessage);
        $this->assertEquals(
            (string) $message,
            (string) $fetchedMessage
        );
    }

    /**
     * @test
     */
    public function it_will_throw_when_no_message_left()
    {
        $this->expectException(EmptyQueueException::class);

        $directory = \sys_get_temp_dir();

        $message = (new GenericMessage())
            ->withHeader(new Date(new \DateTimeImmutable('2017-01-01 18:15:00')));

        $queue = new FilesystemQueue($directory);
        $queue->store($message);

        $queue->fetch();
        $queue->fetch();
    }

    /**
     * @test
     */
    public function it_can_count_messages_in_queue()
    {
        $directory = \sys_get_temp_dir();

        $message = (new GenericMessage())
            ->withHeader(new Date(new \DateTimeImmutable('2017-01-01 18:15:00')));

        $queue = new FilesystemQueue($directory);
        $this->assertCount(0, $queue);

        $queue->store($message);
        $this->assertCount(1, $queue);
    }
}
