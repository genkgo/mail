<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Smtp;

use Genkgo\Mail\EmailAddress;
use Genkgo\Mail\MessageInterface;

interface BackendInterface
{

    /**
     * @param EmailAddress $mailbox
     * @return bool
     */
    public function contains(EmailAddress $mailbox): bool;

    /**
     * @param EmailAddress $mailbox
     * @param MessageInterface $message
     * @param string $folder
     */
    public function store(EmailAddress $mailbox, MessageInterface $message, string $folder): void;

    /**
     * @param EmailAddress $mailbox
     * @param string $folder
     * @param int $number
     * @param int $offset
     * @return iterable
     */
    public function fetch(EmailAddress $mailbox, string $folder, int $number, int $offset = 0): iterable;

    /**
     * @param EmailAddress $mailbox
     * @param string $folder
     * @param string $id
     */
    public function remove(EmailAddress $mailbox, string $folder, string $id): void;

}