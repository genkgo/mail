<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Queue;

use Genkgo\Mail\MessageInterface;
use Genkgo\Mail\Queue\QueueInterface;
use PHPUnit\Framework\TestCase;

abstract class AbstractQueueDecoratorTestCase extends TestCase
{
    /**
     * @test
     */
    public function it_will_use_decorated_store_method()
    {
        $message = $this->createMock(MessageInterface::class);
        $queue = $this->createMock(QueueInterface::class);
        $queue
            ->expects($this->once())
            ->method('store')
        ;

        $this->getDecoratingQueue($queue)->store($message);
    }

    /**
     * @test
     */
    public function it_will_use_decorated_fetch_method()
    {
        $queue = $this->createMock(QueueInterface::class);
        $queue
            ->expects($this->once())
            ->method('fetch')
        ;

        $this->getDecoratingQueue($queue)->fetch();
    }

    abstract protected function getDecoratingQueue(QueueInterface $queue): QueueInterface;
}
