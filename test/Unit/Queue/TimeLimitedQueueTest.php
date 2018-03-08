<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Queue;

use Genkgo\Mail\GenericMessage;
use Genkgo\Mail\Header\GenericHeader;
use Genkgo\Mail\MessageInterface;
use Genkgo\Mail\Queue\ArrayObjectQueue;
use Genkgo\Mail\Queue\QueueInterface;
use Genkgo\Mail\Queue\QueueProcessor;
use Genkgo\Mail\Queue\TimeLimitedQueue;
use Genkgo\Mail\Stream\AsciiEncodedStream;
use Genkgo\Mail\Transport\QueueIfFailedTransport;
use Genkgo\Mail\TransportInterface;

final class TimeLimitedQueueTest extends AbstractQueueDecoratorTestCase
{
    /**
     * @test
     */
    public function it_will_not_expire_messages_when_no_time_limit_is_given(): void
    {
        $storage = new \ArrayObject();

        $queue = new ArrayObjectQueue($storage);
        $queue->store($this->newMessage('Test 1', 1024));
        $queue->store($this->newMessage('Test 2', 2048));
        $queue->store($this->newMessage('Test 3', 3072));
        $queue = new TimeLimitedQueue($queue, 0);

        $transport = $this->createMock(TransportInterface::class);
        $transport
            ->expects($this->at(0))
            ->method('send')
            ->with($this->callback(function (GenericMessage $message) {
                $this->assertEquals('Test 1', $message->getHeader('subject')[0]->getValue());
                return true;
            }));

        $transport
            ->expects($this->at(1))
            ->method('send')
            ->with($this->callback(function (GenericMessage $message) {
                $this->assertEquals('Test 2', $message->getHeader('subject')[0]->getValue());
                return true;
            }));

        $transport
            ->expects($this->at(2))
            ->method('send')
            ->with($this->callback(function (GenericMessage $message) {
                $this->assertEquals('Test 3', $message->getHeader('subject')[0]->getValue());
                return true;
            }));

        $processor = new QueueProcessor($transport, [$queue]);
        $count = $processor->process();

        $this->assertCount(0, $storage);
        $this->assertSame(3, $count);
    }

    /**
     * @test
     */
    public function it_will_expire_messages_when_time_limit_is_reached(): void
    {
        $storage = new \ArrayObject();

        $queue = new ArrayObjectQueue($storage);
        $queue->store($this->newMessage('Test 1', 1024));
        $queue->store($this->newMessage('Test 2', 2048));
        $queue->store($this->newMessage('Test 3', 3072));
        $queue = new TimeLimitedQueue($queue, 2000);

        $transport = $this->createMock(TransportInterface::class);
        $transport
            ->expects($this->once())
            ->method('send')
            ->with($this->callback(function (GenericMessage $message) {
                $this->assertEquals('Test 1', $message->getHeader('subject')[0]->getValue());
                return true;
            }));

        $processor = new QueueProcessor($transport, [$queue]);
        $count = $processor->process();

        $this->assertCount(0, $storage);
        $this->assertSame(1, $count);
    }

    protected function getDecoratingQueue(QueueInterface $queue): QueueInterface
    {
        return new TimeLimitedQueue($queue, 0);
    }

    /**
     * @param string $subject
     * @param int $age
     * @return MessageInterface
     */
    private function newMessage(string $subject, int $age = 0): MessageInterface
    {
        return (new GenericMessage())
            ->withHeader(new GenericHeader(
                QueueIfFailedTransport::QUEUED_HEADER,
                (new \DateTimeImmutable('now'))->modify("-$age seconds")->format('r')
            ))
            ->withHeader(new GenericHeader('Subject', $subject))
            ->withBody(new AsciiEncodedStream($subject));
    }
}
