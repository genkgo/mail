<?php

namespace Genkgo\Mail\Unit\Queue;

use Genkgo\Mail\AbstractTestCase;
use Genkgo\Mail\Exception\ConnectionException;
use Genkgo\Mail\GenericMessage;
use Genkgo\Mail\Header\GenericHeader;
use Genkgo\Mail\MessageInterface;
use Genkgo\Mail\Queue\ArrayObjectQueue;
use Genkgo\Mail\Queue\QueueProcessor;
use Genkgo\Mail\Stream\BitEncodedStream;
use Genkgo\Mail\TransportInterface;

final class ProcessorTest extends AbstractTestCase
{

    /**
     * @test
     */
    public function it_transport_messages_from_queue () {
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

        $processor = new QueueProcessor($transport, [$queue]);
        $processor->process();
    }

    /**
     * @test
     */
    public function it_will_readd_failed_messages_to_queue() {
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
            ->willThrowException(new ConnectionException())
        ;

        $processor = new QueueProcessor($transport, [$queue]);
        $processor->process();

        $this->assertCount(3, $storage);
    }

    /**
     * @param string $subject
     * @return MessageInterface
     */
    private function newMessage(string $subject)
    {
        return (new GenericMessage())
            ->withHeader(new GenericHeader('Subject', $subject))
            ->withBody(new BitEncodedStream($subject));
    }

}