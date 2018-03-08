<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Queue;

use Genkgo\Mail\GenericMessage;
use Genkgo\Mail\Header\GenericHeader;
use Genkgo\Mail\MessageInterface;
use Genkgo\Mail\Queue\ArrayObjectQueue;
use Genkgo\Mail\Queue\LimitQueueProcessor;
use Genkgo\Mail\Queue\QueueProcessorInterface;
use Genkgo\Mail\Stream\AsciiEncodedStream;
use Genkgo\Mail\TransportInterface;

final class LimitQueueProcessorTest extends AbstractQueueProcessorTest
{
    /**
     * @test
     */
    public function it_will_not_limit_messages_when_no_send_limit_is_given(): void
    {
        $storage = new \ArrayObject();

        $queue = new ArrayObjectQueue($storage);
        $queue->store($this->newMessage('Test 1'));
        $queue->store($this->newMessage('Test 2'));
        $queue->store($this->newMessage('Test 3'));

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

        $processor = new LimitQueueProcessor($transport, [$queue], 0);
        $count = $processor->process();

        $this->assertCount(0, $storage);
        $this->assertSame(3, $count);
    }

    /**
     * @test
     */
    public function it_will_stop_sending_messages_when_send_limit_is_reached(): void
    {
        $storage = new \ArrayObject();

        $queue = new ArrayObjectQueue($storage);
        $queue->store($this->newMessage('Test 1'));
        $queue->store($this->newMessage('Test 2'));
        $queue->store($this->newMessage('Test 3'));

        $transport = $this->createMock(TransportInterface::class);
        $transport
            ->expects($this->once())
            ->method('send')
            ->with($this->callback(function (GenericMessage $message) {
                $this->assertEquals('Test 1', $message->getHeader('subject')[0]->getValue());
                return true;
            }));

        $processor = new LimitQueueProcessor($transport, [$queue], 1);
        $count = $processor->process();

        $this->assertCount(2, $storage);
        $this->assertSame(1, $count);
    }

    protected function getQueueProcessor($transport, array $queue): QueueProcessorInterface
    {
        return new LimitQueueProcessor($transport, $queue, 0);
    }

    /**
     * @param string $subject
     * @return MessageInterface
     */
    private function newMessage(string $subject): MessageInterface
    {
        return (new GenericMessage())
            ->withHeader(new GenericHeader('Subject', $subject))
            ->withBody(new AsciiEncodedStream($subject));
    }
}
