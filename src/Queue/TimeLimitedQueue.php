<?php
declare(strict_types=1);

namespace Genkgo\Mail\Queue;

use Genkgo\Mail\MessageInterface;
use Genkgo\Mail\Transport\QueueIfFailedTransport;

final class TimeLimitedQueue implements QueueInterface
{
    /**
     * @var QueueInterface
     */
    private $decoratedQueue;

    /**
     * @var int
     */
    private $timeLimit;

    public function __construct(QueueInterface $queue, int $timeLimit)
    {
        $this->decoratedQueue = $queue;
        $this->timeLimit = $timeLimit;
    }

    /**
     * {@inheritdoc}
     */
    public function store(MessageInterface $message): void
    {
        $this->decoratedQueue->store($message);
    }

    /**
     * {@inheritdoc}
     */
    public function fetch(): MessageInterface
    {
        while ($message = $this->decoratedQueue->fetch()) {
            if ($this->hasMessageExpired($message)) {
                continue;
            }
            break;
        }

        return $message;
    }

    /**
     * @param MessageInterface $message
     * @return bool
     */
    private function hasMessageExpired(MessageInterface $message): bool
    {
        if ($this->timeLimit === 0) {
            return false;
        }
        if ($this->getSubmissionTimestamp($message) + $this->timeLimit > \time()) {
            return false;
        }

        return true;
    }

    /**
     * @param MessageInterface $message
     * @return int
     */
    private function getSubmissionTimestamp(MessageInterface $message): int
    {
        $queuedAt = $message->getHeader(QueueIfFailedTransport::QUEUED_HEADER);

        return (int) \strtotime(\reset($queuedAt)->getValue()->getRaw());
    }
}
