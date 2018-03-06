<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Queue;

use Genkgo\TestMail\AbstractTestCase;
use Genkgo\Mail\Exception\EmptyQueueException;
use Genkgo\Mail\GenericMessage;
use Genkgo\Mail\Header\Date;
use Genkgo\Mail\Queue\ArrayObjectQueue;

final class ArrayObjectQueueTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_can_store_a_message_in_array_object()
    {
        $message = (new GenericMessage())
            ->withHeader(new Date(new \DateTimeImmutable('2017-01-01 18:15:00')));

        $storage = new \ArrayObject();
        $queue = new ArrayObjectQueue($storage);
        $queue->store($message);

        $this->assertEquals(
            (string) $message,
            $storage[0]
        );
    }

    /**
     * @test
     */
    public function it_can_fetch_a_message_from_the_array_object()
    {
        $message = (new GenericMessage())
            ->withHeader(new Date(new \DateTimeImmutable('2017-01-01 18:15:00')));

        $storage = new \ArrayObject();
        $queue = new ArrayObjectQueue($storage);
        $queue->store($message);

        $this->assertEquals(
            (string) $message,
            (string) $queue->fetch()
        );
    }

    /**
     * @test
     */
    public function it_will_throw_when_no_message_left()
    {
        $this->expectException(EmptyQueueException::class);

        $message = (new GenericMessage())
            ->withHeader(new Date(new \DateTimeImmutable('2017-01-01 18:15:00')));

        $storage = new \ArrayObject();
        $queue = new ArrayObjectQueue($storage);
        $queue->store($message);

        $queue->fetch();
        $queue->fetch();
    }

    /**
     * @test
     */
    public function it_can_count_messages_in_queue()
    {
        $message = (new GenericMessage())
            ->withHeader(new Date(new \DateTimeImmutable('2017-01-01 18:15:00')));

        $storage = new \ArrayObject();
        $queue = new ArrayObjectQueue($storage);
        $this->assertCount(0, $queue);

        $queue->store($message);
        $this->assertCount(1, $queue);

        $queue->store($message);
        $this->assertCount(2, $queue);
    }
}
