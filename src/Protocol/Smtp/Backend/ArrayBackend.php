<?php

namespace Genkgo\Mail\Protocol\Smtp\Backend;

use Genkgo\Mail\EmailAddress;
use Genkgo\Mail\MessageInterface;
use Genkgo\Mail\Protocol\Smtp\BackendInterface;

final class ArrayBackend implements BackendInterface
{
    /**
     * @var array
     */
    private $addresses;
    /**
     * @var \ArrayAccess
     */
    private $backend;

    /**
     * ArrayBackend constructor.
     * @param array $addresses
     * @param \ArrayAccess $backend
     */
    public function __construct(array $addresses, \ArrayAccess $backend)
    {
        $this->addresses = array_combine(
            $addresses,
            array_fill(0, count($addresses), true)
        );
        $this->backend = $backend;
    }

    /**
     * @param EmailAddress $mailbox
     * @return bool
     */
    public function contains(EmailAddress $mailbox): bool
    {
        return isset($this->addresses[(string)$mailbox]);
    }

    /**
     * @param EmailAddress $mailbox
     * @param MessageInterface $message
     * @param string $folder
     */
    public function store(EmailAddress $mailbox, MessageInterface $message, string $folder): void
    {
        if (!isset($this->backend[(string)$mailbox])) {
            $this->backend[(string)$mailbox] = [];
        }

        if (!isset($this->backend[(string)$mailbox][$folder])) {
            $this->backend[(string)$mailbox][$folder] = [];
        }

        $this->backend[(string)$mailbox][$folder][] = $mailbox;
    }

    /**
     * @param EmailAddress $mailbox
     * @param string $folder
     * @param int $number
     * @param int $offset
     * @return iterable
     */
    public function fetch(EmailAddress $mailbox, string $folder, int $number, int $offset = 0): iterable
    {
        return new \LimitIterator(
            new \ArrayIterator(
                $this->backend[(string)$mailbox][$folder] ?? []
            ),
            $offset,
            $number
        );
    }

    /**
     * @param EmailAddress $mailbox
     * @param string $folder
     * @param string $id
     */
    public function remove(EmailAddress $mailbox, string $folder, string $id): void
    {
        unset($this->backend[(string)$mailbox][$folder][$id]);
    }
}