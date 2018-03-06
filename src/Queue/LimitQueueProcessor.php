<?php
declare(strict_types=1);

namespace Genkgo\Mail\Queue;

use Genkgo\Mail\Exception\AbstractProtocolException;
use Genkgo\Mail\Exception\EmptyQueueException;
use Genkgo\Mail\TransportInterface;

final class LimitQueueProcessor implements QueueProcessorInterface
{
    /**
     * @var TransportInterface
     */
    private $transport;

    /**
     * @var iterable|QueueInterface[]
     */
    private $queue;

    /**
     * @var int
     */
    private $limit;

    /**
     * @param TransportInterface $transport
     * @param QueueInterface[] $queue
     */
    public function __construct(TransportInterface $transport, iterable $queue, int $limit)
    {
        $this->transport = $transport;
        $this->queue = $queue;
        $this->limit = $limit;
    }

    /**
     * {@inheritdoc}
     */
    public function process(): int
    {
        $count = 0;
        foreach ($this->queue as $queue) {
            try {
                while ($message = $queue->fetch()) {
                    if ($this->limit > 0 && $this->limit <= $count) {
                        $queue->store($message);

                        return $count;
                    }
                    try {
                        $this->transport->send($message);
                    } catch (AbstractProtocolException $e) {
                        $queue->store($message);

                        return $count;
                    }
                    ++$count;
                }
            } catch (EmptyQueueException $e) {
            }
        }

        return $count;
    }
}
