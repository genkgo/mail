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
     * ArrayBackend constructor.
     * @param array $addresses
     */
    public function __construct(array $addresses)
    {
        $this->addresses = array_combine(
            $addresses,
            array_fill(0, count($addresses), true)
        );
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
        ;
    }
}