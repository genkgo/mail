<?php
declare(strict_types=1);

namespace Genkgo\Mail\Queue;

interface QueueProcessorInterface
{
    /**
     * Process the queues and attempt to send stored messages.
     * If the transport is unable to handle the transaction, messages will be
     * returned to the queue storage and the method will return.
     * @return int
     */
    public function process(): int;
}
