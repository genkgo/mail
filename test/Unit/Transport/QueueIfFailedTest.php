<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Transport;

use Genkgo\TestMail\AbstractTestCase;
use Genkgo\Mail\Exception\ConnectionRefusedException;
use Genkgo\Mail\Exception\QueueIfFailedException;
use Genkgo\Mail\Exception\QueueStoreException;
use Genkgo\Mail\GenericMessage;
use Genkgo\Mail\MessageInterface;
use Genkgo\Mail\Queue\QueueInterface;
use Genkgo\Mail\Transport\QueueIfFailedTransport;
use Genkgo\Mail\TransportInterface;

final class QueueIfFailedTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_queues_when_transport_fails()
    {
        $transport = $this->createMock(TransportInterface::class);
        $queue = $this->createMock(QueueInterface::class);

        $transport
            ->expects($this->once())
            ->method('send')
            ->willThrowException(new ConnectionRefusedException());

        $queue
            ->expects($this->any())
            ->method('store')
            ->with($this->isInstanceOf(GenericMessage::class));

        $wrapper = new QueueIfFailedTransport([$transport], [$queue]);
        $wrapper->send(new GenericMessage());
        $wrapper->send(new GenericMessage());
    }

    /**
     * @test
     */
    public function it_tries_multiple_transports_before_queueing()
    {
        $transport1 = $this->createMock(TransportInterface::class);
        $transport2 = $this->createMock(TransportInterface::class);
        $storage = $this->createMock(QueueInterface::class);

        $transport1
            ->expects($this->any())
            ->method('send')
            ->willThrowException(new ConnectionRefusedException());

        $transport2
            ->expects($this->any())
            ->method('send');

        $storage
            ->expects($this->never())
            ->method('store');

        $wrapper = new QueueIfFailedTransport([$transport1, $transport2], [$storage]);
        $wrapper->send(new GenericMessage());
        $wrapper->send(new GenericMessage());
    }

    /**
     * @test
     */
    public function it_tries_multiple_queues()
    {
        $transport1 = $this->createMock(TransportInterface::class);
        $transport2 = $this->createMock(TransportInterface::class);
        $queue1 = $this->createMock(QueueInterface::class);
        $queue2 = $this->createMock(QueueInterface::class);

        $transport1
            ->expects($this->once())
            ->method('send')
            ->willThrowException(new ConnectionRefusedException());

        $transport2
            ->expects($this->once())
            ->method('send')
            ->willThrowException(new ConnectionRefusedException());

        $queue1
            ->expects($this->any())
            ->method('store')
            ->willThrowException(new QueueStoreException());

        $queue2
            ->expects($this->any())
            ->method('store');

        $wrapper = new QueueIfFailedTransport([$transport1, $transport2], [$queue1, $queue2]);
        $wrapper->send(new GenericMessage());
        $wrapper->send(new GenericMessage());
    }

    /**
     * @test
     */
    public function it_throws_when_transport_and_queue_fails()
    {
        $this->expectException(QueueIfFailedException::class);

        $transport1 = $this->createMock(TransportInterface::class);
        $transport2 = $this->createMock(TransportInterface::class);
        $queue1 = $this->createMock(QueueInterface::class);
        $queue2 = $this->createMock(QueueInterface::class);

        $transport1
            ->expects($this->once())
            ->method('send')
            ->willThrowException(new ConnectionRefusedException());

        $transport2
            ->expects($this->once())
            ->method('send')
            ->willThrowException(new ConnectionRefusedException());

        $queue1
            ->expects($this->once())
            ->method('store')
            ->willThrowException(new QueueStoreException());

        $queue2
            ->expects($this->once())
            ->method('store')
            ->willThrowException(new QueueStoreException());

        $wrapper = new QueueIfFailedTransport([$transport1, $transport2], [$queue1, $queue2]);
        $wrapper->send(new GenericMessage());
        $wrapper->send(new GenericMessage());
    }

    /**
     * @test
     */
    public function it_only_adds_queued_at_header_once()
    {
        $transport = $this->createMock(TransportInterface::class);
        $queue = $this->createMock(QueueInterface::class);

        $transport
            ->expects($this->once())
            ->method('send')
            ->willThrowException(new ConnectionRefusedException());

        $queuedMessage = new GenericMessage();

        $queue
            ->expects($this->exactly(2))
            ->method('store')
            ->with(
                $this->callback(
                    function (MessageInterface $message) use (&$queuedMessage) {
                        $this->assertCount(1, $message->getHeader(QueueIfFailedTransport::QUEUED_HEADER));
                        $queuedMessage = $message;
                        return true;
                    }
                )
            );

        $message = new GenericMessage();
        $wrapper = new QueueIfFailedTransport([$transport], [$queue]);

        $wrapper->send($message);
        $wrapper->send($queuedMessage);
    }
}
