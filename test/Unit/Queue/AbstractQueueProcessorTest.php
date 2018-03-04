<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Queue;

use Genkgo\Mail\Exception\AbstractProtocolException;
use Genkgo\Mail\Exception\ConnectionRefusedException;
use Genkgo\Mail\GenericMessage;
use Genkgo\Mail\Header\GenericHeader;
use Genkgo\Mail\MessageInterface;
use Genkgo\Mail\Queue\ArrayObjectQueue;
use Genkgo\Mail\Queue\QueueProcessorInterface;
use Genkgo\Mail\Stream\AsciiEncodedStream;
use Genkgo\Mail\TransportInterface;
use Genkgo\TestMail\AbstractTestCase;

abstract class AbstractQueueProcessorTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_transport_messages_from_queue()
    {
        $queue = new ArrayObjectQueue(new \ArrayObject());
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

        $processor = $this->getQueueProcessor($transport, [$queue]);
        $count = $processor->process();

        $this->assertSame(3, $count);
    }

    /**
     * @test
     */
    public function it_will_readd_failed_messages_to_queue()
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
            }))
            ->willThrowException(new ConnectionRefusedException())
        ;

        $processor = $this->getQueueProcessor($transport, [$queue]);
        $count = $processor->process();

        $this->assertCount(3, $storage);
        $this->assertSame(0, $count);
    }

    /**
     * @test
     */
    public function it_will_readd_messages_resulting_in_any_protocol_exception()
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
            }))
            ->willThrowException(new class extends AbstractProtocolException {
            })
        ;

        $processor = $this->getQueueProcessor($transport, [$queue]);
        $count = $processor->process();

        $this->assertCount(3, $storage);
        $this->assertSame(0, $count);
    }

    /**
     * @param string $subject
     * @return MessageInterface
     */
    private function newMessage(string $subject)
    {
        return (new GenericMessage())
            ->withHeader(new GenericHeader('Subject', $subject))
            ->withBody(new AsciiEncodedStream($subject));
    }

    abstract protected function getQueueProcessor($transport, array $queue): QueueProcessorInterface;
}
