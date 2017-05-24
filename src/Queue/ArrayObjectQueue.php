<?php
declare(strict_types=1);

namespace Genkgo\Mail\Queue;

use Genkgo\Mail\Exception\EmptyQueueException;
use Genkgo\Mail\GenericMessage;
use Genkgo\Mail\MessageInterface;

/**
 * Class ArrayObjectQueue
 * @package Genkgo\Email\Queue
 */
final class ArrayObjectQueue implements QueueInterface
{
    /**
     * @var \ArrayObject
     */
    private $storage;

    /**
     * ArrayObjectQueue constructor.
     * @param \ArrayObject $storage
     */
    public function __construct(\ArrayObject $storage)
    {
        $this->storage = $storage;
    }

    /**
     * @param MessageInterface $message
     */
    public function store(MessageInterface $message): void
    {
        $this->storage->append((string)$message);
    }

    /**
     * @return MessageInterface
     * @throws EmptyQueueException
     */
    public function fetch(): MessageInterface
    {
        foreach ($this->storage as $key => $item) {
            unset($this->storage[$key]);
            return GenericMessage::fromString($item);
        }

        throw new EmptyQueueException('No message left in queue');
    }
}