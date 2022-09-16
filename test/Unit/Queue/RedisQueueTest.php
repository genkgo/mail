<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Queue;

use Genkgo\Mail\Exception\EmptyQueueException;
use Genkgo\Mail\Exception\QueueStoreException;
use Genkgo\Mail\GenericMessage;
use Genkgo\Mail\Header\Date;
use Genkgo\Mail\MessageInterface;
use Genkgo\Mail\Queue\RedisQueue;
use Genkgo\TestMail\AbstractTestCase;
use Predis\ClientInterface;
use Predis\Connection\ConnectionException;

final class RedisQueueTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_can_store_a_message_in_redis(): void
    {
        $message = (new GenericMessage())
            ->withHeader(new Date(new \DateTimeImmutable('2017-01-01 18:15:00')));

        $client = $this->createMock(ClientInterface::class);

        $client
            ->expects($this->once())
            ->method('__call')
            ->with('rpush', ['queue', [(string)$message]]);

        $queue = new RedisQueue($client, 'queue');
        $queue->store($message);
    }

    /**
     * @test
     */
    public function it_can_fetch_a_message_from_redis(): void
    {
        $message = (new GenericMessage())
            ->withHeader(new Date(new \DateTimeImmutable('2017-01-01 18:15:00')));

        $client = $this->createMock(ClientInterface::class);

        $client
            ->expects($this->exactly(2))
            ->method('__call')
            ->withConsecutive(
                ['rpush', ['queue', [(string)$message]]],
                ['lpop', ['queue']]
            )
            ->willReturnOnConsecutiveCalls(
                null,
                (string)$message
            );

        $queue = new RedisQueue($client, 'queue');
        $queue->store($message);

        $this->assertEquals(
            (string) $message,
            (string) $queue->fetch()
        );
    }

    /**
     * @test
     */
    public function it_will_throw_when_no_message_left(): void
    {
        $this->expectException(EmptyQueueException::class);

        $message = (new GenericMessage())
            ->withHeader(new Date(new \DateTimeImmutable('2017-01-01 18:15:00')));

        $client = $this->createMock(ClientInterface::class);

        $client
            ->expects($this->exactly(3))
            ->method('__call')
            ->withConsecutive(
                ['rpush', ['queue', [(string)$message]]],
                ['lpop', ['queue']],
                ['lpop', ['queue']]
            )
            ->willReturnOnConsecutiveCalls(
                [],
                (string)$message,
                $this->throwException(new EmptyQueueException())
            );

        $queue = new RedisQueue($client, 'queue');
        $queue->store($message);

        $queue->fetch();
        $queue->fetch();
    }

    /**
     * @test
     */
    public function it_can_count_messages_in_queue(): void
    {
        $client = $this->createMock(ClientInterface::class);

        $client
            ->expects($this->exactly(2))
            ->method('__call')
            ->withConsecutive(
                ['llen', ['queue']],
                ['llen', ['queue']]
            )
            ->willReturnOnConsecutiveCalls(0, 2);

        $queue = new RedisQueue($client, 'queue');

        $this->assertCount(0, $queue);
        $this->assertCount(2, $queue);
    }

    /**
     * @test
     */
    public function it_catches_connection_exception_in_store(): void
    {
        $this->expectException(QueueStoreException::class);
        $this->expectExceptionMessageMatches('/Cannot add message to redis queue/');

        $message = $this->createMock(MessageInterface::class);
        $client = $this->newConnectionExceptionClient('rpush', ['queue', ['']]);
        $queue = new RedisQueue($client, 'queue');

        $queue->store($message);
    }

    /**
     * @test
     */
    public function it_catches_connection_exception_in_fetch(): void
    {
        $this->expectException(QueueStoreException::class);
        $this->expectExceptionMessageMatches('/Cannot add message to redis queue/');

        $client = $this->newConnectionExceptionClient('lpop', ['queue']);
        $queue = new RedisQueue($client, 'queue');

        $queue->fetch();
    }

    /**
     * @test
     */
    public function it_catches_connection_exception_in_count(): void
    {
        $this->expectException(QueueStoreException::class);
        $this->expectExceptionMessageMatches('/Cannot get messages from redis queue/');

        $client = $this->newConnectionExceptionClient('llen', ['queue']);
        $queue = new RedisQueue($client, 'queue');

        $queue->count();
    }

    private function newConnectionExceptionClient(string $method, array $args): ClientInterface
    {
        $connectionException = $this->createMock(ConnectionException::class);
        $client = $this->createMock(ClientInterface::class);
        $client
            ->expects($this->once())
            ->method('__call')
            ->with($method, $args)
            ->willThrowException($connectionException)
        ;

        return $client;
    }
}
