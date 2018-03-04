<?php
declare(strict_types=1);

namespace Genkgo\Mail\Queue;

use Genkgo\Mail\Exception\EmptyQueueException;
use Genkgo\Mail\Exception\QueueStoreException;
use Genkgo\Mail\MessageInterface;

interface QueueInterface
{
    /**
     * @param MessageInterface $message
     * @return void
     * @throws QueueStoreException
     */
    public function store(MessageInterface $message): void;

    /**
     * @return MessageInterface
     * @throws EmptyQueueException
     */
    public function fetch(): MessageInterface;
}
