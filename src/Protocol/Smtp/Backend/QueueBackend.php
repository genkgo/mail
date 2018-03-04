<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Smtp\Backend;

use Genkgo\Mail\EmailAddress;
use Genkgo\Mail\MessageInterface;
use Genkgo\Mail\Protocol\Smtp\BackendInterface;
use Genkgo\Mail\Queue\QueueInterface;

final class QueueBackend implements BackendInterface
{
    /**
     * @var QueueInterface
     */
    private $queue;

    /**
     * @param QueueInterface $backend
     */
    public function __construct(QueueInterface $backend)
    {
        $this->queue = $backend;
    }

    /**
     * @param EmailAddress $mailbox
     * @return bool
     */
    public function contains(EmailAddress $mailbox): bool
    {
        return true;
    }

    /**
     * @param EmailAddress $mailbox
     * @param MessageInterface $message
     * @param string $folder
     */
    public function store(EmailAddress $mailbox, MessageInterface $message, string $folder): void
    {
        $this->queue->store($message);
    }
}
