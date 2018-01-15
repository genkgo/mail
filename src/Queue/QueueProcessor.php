<?php
declare(strict_types=1);

namespace Genkgo\Mail\Queue;

use Genkgo\Mail\Exception\AbstractProtocolException;
use Genkgo\Mail\Exception\EmptyQueueException;
use Genkgo\Mail\TransportInterface;

/**
 * Class QueueProcessor
 * @package Genkgo\Mail\Queue
 */
final class QueueProcessor
{

    /**
     * @var TransportInterface
     */
    private $transport;
    /**
     * @var array|QueueInterface[]
     */
    private $queue;

    /**
     * @param TransportInterface $transport
     * @param QueueInterface[] $queue
     */
    public function __construct(TransportInterface $transport, iterable $queue)
    {
        $this->transport = $transport;
        $this->queue = $queue;
    }

    /**
     *
     */
    public function process(): int
    {
        $count = 0;
        foreach ($this->queue as $queue) {
            try {
                while ($message = $queue->fetch()) {
                    try {
                        $this->transport->send($message);
                    } catch (AbstractProtocolException $e) {
                        $queue->store($message);

                        // do not continue transporting messages
                        // apparently our transport is not ready to receive messages yet
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
