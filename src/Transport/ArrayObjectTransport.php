<?php
declare(strict_types=1);

namespace Genkgo\Mail\Transport;

use Genkgo\Mail\MessageInterface;
use Genkgo\Mail\TransportInterface;

final class ArrayObjectTransport implements TransportInterface
{
    /**
     * @var \ArrayObject
     */
    private $storage;

    /**
     * @param \ArrayObject $storage
     */
    public function __construct(\ArrayObject $storage)
    {
        $this->storage = $storage;
    }

    /**
     * @param MessageInterface $message
     * @return void
     */
    public function send(MessageInterface $message): void
    {
        $this->storage->append($message);
    }
}
