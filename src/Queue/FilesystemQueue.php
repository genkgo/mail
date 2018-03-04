<?php
declare(strict_types=1);

namespace Genkgo\Mail\Queue;

use Genkgo\Mail\Exception\EmptyQueueException;
use Genkgo\Mail\GenericMessage;
use Genkgo\Mail\MessageInterface;

final class FilesystemQueue implements QueueInterface, \Countable
{
    /**
     * @var string
     */
    private $directory;

    /**
     * @var int
     */
    private $mode;

    /**
     * @param string $directory
     * @param int $mode
     */
    public function __construct(string $directory, int $mode = 0750)
    {
        $this->directory = $directory;
        $this->mode = $mode;
    }

    /**
     * @param MessageInterface $message
     */
    public function store(MessageInterface $message): void
    {
        $messageString = (string)$message;
        $filename = \hash('sha256', $messageString) . '.eml';

        \file_put_contents(
            $this->directory . '/' . $filename,
            $messageString
        );

        \chmod($this->directory . '/' . $filename, $this->mode);
    }

    /**
     * @return MessageInterface
     * @throws EmptyQueueException
     */
    public function fetch(): MessageInterface
    {
        $queue = new \GlobIterator($this->directory . '/*.eml');
        /** @var \SplFileInfo $item */
        foreach ($queue as $item) {
            $messageString = \file_get_contents($item->getPathname());
            \unlink($item->getPathname());
            return GenericMessage::fromString($messageString);
        }

        throw new EmptyQueueException('No message left in queue');
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return (new \GlobIterator($this->directory . '/*.eml'))->count();
    }
}
